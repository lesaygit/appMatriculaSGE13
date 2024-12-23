<?php

class Localidad extends CI_Model {
    /*
     * Inserta una nueva localidad
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se pudo o no insertar
     *  0: Fallo en la configuración
     *  1: Si la localidad ya existe en el municipio establecido en la configuración
     */

    public function insertar($nombre) {
        //Obtener el municipio a partir de la configuración
        $result = $this->db->query('SELECT l.idMunicipio FROM localidades l INNER JOIN configuracion c ON c.localidad = l.idLocalidad');

        if ($result === FALSE)
            return null;

        $idMunicipio = $result->num_rows() == 1 ? $result->row()->idMunicipio : null;

        if (empty($idMunicipio)) //Mala configuración
            return 0;

        //Verificar si no existe otra localidad con el mismo nombre
        $res = $this->db->query('
            SELECT
                CASE WHEN EXISTS(
                            SELECT *
                            FROM localidades
                            WHERE idMunicipio = ? and nombre = ?)
                THEN 1 ELSE 0 END as exist', array($idMunicipio, $nombre));

        if ($res === FALSE)
            return null;

        $row = $res->row();

        if ($row->exist == 1)
            return 1; //Ya existe otra localidad con el nombre especificado

        $this->db->trans_start();
        $this->db->insert('localidades', array('idMunicipio' => $idMunicipio, 'nombre' => $nombre));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Actualiza el nombre de una localidad
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se pudo o no actualizar
     *  1: Si la localidad ya existe en el municipio establecido en la configuración
     */

    public function actualizar($idLocalidad, $nombre) {
        $result = $this->db->query('
            SELECT idLocalidad
            FROM localidades
            WHERE idLocalidad <> ? and nombre = ? and idMunicipio = (SELECT l.idMunicipio FROM localidades l INNER JOIN configuracion c ON c.localidad = l.idLocalidad)', array($idLocalidad, $nombre));

        if ($result === FALSE)
            return null;

        if ($result->num_rows() > 0)
            return 1;

        $this->db->trans_start();
        $this->db->update('localidades', array('nombre' => $nombre), array('idLocalidad' => $idLocalidad));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Elimina una localidad
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se pudo o no eliminar
     */

    public function eliminar($idLocalidad) {
        $this->db->trans_start();
        $this->db->delete('localidades', array('idLocalidad' => $idLocalidad));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Obtiene todas las localidades del municipio al que pertenece la localidad
     * establecida en la configuración
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Con las localidads obtenidas
     *      Si $checkUse = false: La llave es el ID y el valor es el nombre.
     *      Si $checkUse = true: La llave es el ID y el valor es un array con las llaves: nombre y usado.
     */

    public function lista($prepend = null, $checkUse = false) {
        $query = $this->db->query('
            SELECT
                l.idLocalidad, l.nombre' .
                ($checkUse ? ',
                    case when exists(select * from estudiantes e where e.localidad = l.idLocalidad) or
                              exists(select * from configuracion c where c.localidad = l.idLocalidad)
                    then 1 else 0 end as usado ' : ' ') .
           'FROM localidades l
            WHERE l.idMunicipio = (SELECT loc.idMunicipio FROM localidades loc INNER JOIN configuracion c ON c.localidad = loc.idLocalidad)');

        if ($query === FALSE)
            return null;

        $result = is_array($prepend) ? $prepend : array();
        foreach ($query->result() as $row)
            $result[$row->idLocalidad] = $checkUse ? array('nombre' => $row->nombre, 'usado' => $row->usado) : $row->nombre;

        return $result;
    }

}

?>