<?php

class Usuario extends CI_Model {
    /*
     * Inserta un nuevo usuario
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se pudo o no insertar
     *  1: Si ya existe un usuario con la misma cuenta
     */

    public function insertar($login, $nombre, $apellidos, $pw, $roles) {
        $login = strtolower(trim($login));

        //Verificar si existe otro usuario con el login especificado
        $res = $this->db->query('
            SELECT
                CASE WHEN EXISTS(
                            SELECT *
                            FROM usuarios
                            WHERE login = ?)
                THEN 1 ELSE 0 END as exist', array($login));

        if ($res === FALSE)
            return null;

        $row = $res->row();

        if ($row->exist == 1)
            return 1; //Ya existe el login especificado

        $this->db->trans_start();
        $this->db->insert('usuarios', array('login' => $login, 'nombre' => $nombre, 'apellidos' => $apellidos, 'password' => $pw));

        if ($roles) {
            $idUsuario = $this->db->insert_id();
            $roles_usr = array();
            foreach ($roles as $i => $idRol)
                if ($idRol != 1)
                    $roles_usr[] = array('idusuario' => $idUsuario, 'idrol' => $idRol);

            if ($roles_usr)
                $this->db->insert_batch('usuarios_roles', $roles_usr);
        }
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Actualiza los datos de un usuario
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se pudo o no insertar
     *  1: Si ya existe un usuario con la misma cuenta
     */

    public function actualizar($idUsuario, $login, $nombre, $apellidos, $roles) {
        $data = array('nombre' => $nombre, 'apellidos' => $apellidos);

        if ($login = strtolower(trim($login))) {
            $result = $this->db->query('SELECT idusuario FROM usuarios WHERE idusuario <> ? and login = ?', array($idUsuario, $login));

            if ($result === FALSE)
                return null;

            if ($result->num_rows() > 0)
                return 1;

            $data['login'] = $login;
        }

        $this->db->trans_start();
        $this->db->update('usuarios', $data, array('idusuario' => $idUsuario));

        if ($roles) {
            $user_roles = array();
            foreach ($roles as $i => $idRol)
                if ($idRol != 1)
                    $user_roles[] = array('idusuario' => $idUsuario, 'idrol' => $idRol);

            $this->db->delete('usuarios_roles', array('idusuario' => $idUsuario));
            if ($user_roles)
                $this->db->insert_batch('usuarios_roles', $user_roles);
        }
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Actualiza la contraseña de un usuario
     * Devuelve:
     *  TRUE/FALSE: Si se cambió o no la contraseña
     */

    public function actualizar_password($idUsuario, $newPassword, $oldPassword = '') {
        if ($oldPassword) {
            $query = $this->db->query('SELECT * FROM usuarios WHERE idUsuario = ? and password = ?', array($idUsuario, $oldPassword));
            if ($query === FALSE)
                return null;

            if ($query->num_rows() != 1)
                return false;
        }

        $this->db->trans_start();
        $this->db->update('usuarios', array('password' => $newPassword), array('idusuario' => $idUsuario));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Determina si es válida la combinación de usuario/contraseña
     * Devuelve
     *   NULL: Si hubo error
     *   FALSE: Si no es válida
     *   Objeto: Si es válida. Llaves: idusuario,login,nombre,apellidos,password,tema
     */

    public function validar_password($user, $password) {
        $query = $this->db->query('SELECT * FROM usuarios WHERE login = ? and password = ?', array(strtolower($user), $password));

        if ($query === false)
            return null;

        if ($query->num_rows() == 0)
            return false;

        return $query->row();
    }

    /*
     * Elimina un usuario
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se pudo o no eliminar
     *  -1: No se puede eliminar el usuario administrador
     */

    public function eliminar($idUsuario) {
        $isAdmin = $this->es_administrador($idUsuario);

        if (is_null($isAdmin))
            return null;

        if ($isAdmin)
            return -1; //No se puede eliminar el usuario administrador

        $this->db->trans_start();
        $this->db->delete('usuarios', array('idusuario' => $idUsuario));
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
        $query = $this->db->query('SELECT idusuario, login, nombre, apellidos FROM usuarios');

        if ($query === FALSE)
            return null;

        $r = array();
        foreach ($query->result_array() as $row) {
            $id = $row['idusuario'];
            unset($row['idusuario']);
            $r[$id] = $row;
        }

        return $r;
    }

    /*
     * Devuelve los permisos que tiene el usuario especificado
     * Devuelve:
     *  Array si se pudo obtener los permisos
     *  NULL si hubo error
     */

    public function permisos($idUsuario) {
        $query = $this->db->query('
            SELECT DISTINCT p.idpermiso
            FROM usuarios u
                INNER JOIN usuarios_roles ur ON ur.idusuario = u.idusuario
                INNER JOIN permisos_roles pr ON pr.idrol = ur.idrol
                INNER JOIN permisos p ON p.idpermiso = pr.idpermiso
            WHERE u.idUsuario = ?', array($idUsuario));

        $permisos[] = array();

        if ($query === FALSE)
            return null;

        foreach ($query->result() as $row)
            $permisos[] = $row->idpermiso;

        return $permisos;
    }

    /*
     * Determina si el usuario activo tiene el permiso especificado. Se toman los permisos almacenados en la sesión
     * Devuelve:
     *  TRUE/FALSE Si tiene o no el permiso
     */

    public function tiene_permiso($idPermiso) {
        return in_array($idPermiso, $this->session_values->permisos);
    }

    /*
     * Determina si el usuario especificado tiene el rol de Administrador
     */

    public function es_administrador($idUsuario) {
        $query = $this->db->query('
            SELECT
                CASE WHEN EXISTS(SELECT * FROM usuarios_roles WHERE idusuario = ? and idrol = 1)
                THEN 1 ELSE 0 END as exist', array($idUsuario));

        return $query === FALSE ? null : $query->row()->exist == 1;
    }

    /*
     * Obtiene los roles que tiene el usuario especificado
     * Devuelve:
     *  Array si se pudo obtener los permisos.
     *  NULL si hubo error
     */

    public function roles($idUsuario) {
        $query = $this->db->query('SELECT idrol FROM usuarios_roles WHERE idusuario = ?', array($idUsuario));

        $roles[] = array();

        if ($query === FALSE)
            return null;

        foreach ($query->result() as $row)
            $roles[] = $row->idrol;

        return $roles;
    }

    /*
     * Actualiza el tema (apariencia del sitio) de un usuario
     * Devuelve:
     *  TRUE/FALSE: Si se pudo actualizar
     */

    public function actualizar_tema($idUsuario, $tema) {
        $tema = strtolower($tema);

        if (!in_array($tema, $this->common_model->temas()))
            return false;

        $this->db->trans_start();
        $this->db->update('usuarios', array('tema' => $tema), array('idusuario' => $idUsuario));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

}

?>