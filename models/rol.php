<?php

class Rol extends CI_Model {
    /*
     * Inserta un nuevo registro
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se pudo o no insertar
     *  1: Si ya existe un registro con el mismo nombre
     */

    public function insertar($nombre, $permisos) {
        //Verificar si no existe otro registro con el mismo nombre
        $res = $this->db->query('
            SELECT
                CASE WHEN EXISTS(
                            SELECT *
                            FROM roles
                            WHERE nombre = ?)
                THEN 1 ELSE 0 END as exist', array($nombre));

        if ($res === FALSE)
            return null;

        $row = $res->row();

        if ($row->exist == 1)
            return 1; //Ya existe otro registro con el nombre especificado

        $this->db->trans_start();
        $this->db->insert('roles', array('nombre' => $nombre));
        if ($permisos) {
            $idRol = $this->db->insert_id();
            $permisos_roles = array();
            foreach ($permisos as $i => $permiso)
                $permisos_roles[] = array('idrol' => $idRol, 'idpermiso' => $permiso);
            $this->db->insert_batch('permisos_roles', $permisos_roles);
        }
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Actualiza un registro
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se pudo o no actualizar
     *  1: Si existe otro registro con el nombre nuevo
     */

    public function actualizar($idRol, $nombre, $permisos) {
        $result = $this->db->query('SELECT idrol FROM roles WHERE idrol <> ? and nombre = ?', array($idRol, $nombre));

        if ($result === FALSE)
            return null;

        if ($result->num_rows() > 0)
            return 1;

        $this->db->trans_start();
        $this->db->update('roles', array('nombre' => $nombre), array('idrol' => $idRol));
        $this->db->delete('permisos_roles', array('idrol' => $idRol));
        if ($permisos) {
            $permisos_roles = array();

            foreach ($permisos as $i => $permiso)
                $permisos_roles[] = array('idrol' => $idRol, 'idpermiso' => $permiso);

            $this->db->insert_batch('permisos_roles', $permisos_roles);
        }
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Elimina un registro
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se pudo o no eliminar. False también si se intenta eliminar el rol=1 (administrador)
     */

    public function eliminar($idRol) {
        if ($idRol == 1)
            return false;

        $this->db->trans_start();
        $this->db->delete('roles', array('idrol' => $idRol));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Obtiene todos los registros
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Contiene los registros obtenidos. La llave es el ID y el valor es el nombre.
     */

    public function lista() {
        $query = $this->db->query('SELECT idrol, nombre FROM roles');

        if ($query === FALSE)
            return null;

        $result = array();

        foreach ($query->result() as $row)
            $result[$row->idrol] = $row->nombre;

        return $result;
    }

    /*
     * Obtiene todos los roles que posee un usuario
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Contiene los registros obtenidos. La llave es el ID y el valor es el nombre.
     */

    public function usuario($idUsuario) {
        $query = $this->db->query('SELECT idrol FROM usuarios_roles WHERE idusuario = ?', array($idUsuario));

        if ($query === FALSE)
            return null;

        $result = array();

        foreach ($query->result() as $row)
            $result[] = $row->idrol;

        return $result;
    }

    /*
     * Obtiene la lista de los permisos asignados a un rol
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Contiene los registros obtenidos. La llave es el ID y el valor es el nombre.
     */

    public function permisos($idRol) {
        $query = $this->db->query('SELECT idpermiso FROM permisos_roles WHERE idrol = ?', array($idRol));

        if ($query === FALSE)
            return null;

        $result = array();

        foreach ($query->result() as $row)
            $result[] = $row->idpermiso;

        return $result;
    }

}

?>