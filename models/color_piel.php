<?php

class Color_piel extends CI_Model {
/*
     * Inserta un nuevo registro
     * Devuelve:
     *  NULL: Si hubo error
     *  TRUE/FALSE: Si se pudo o no insertar
     *  1: Si ya existe el color
     */

    public function insertar($nombre) {
        //Verificar si no existe otro registro con el mismo nombre
        $res = $this->db->query('
            SELECT
                CASE WHEN EXISTS(
                            SELECT *
                            FROM colores_piel
                            WHERE Color = ?)
                THEN 1 ELSE 0 END as exist', array($nombre));

        if ($res === FALSE)
            return null;

        if ($res->row()->exist == 1)
            return 1; //Ya existe otro registro con el nombre especificado

        $this->db->trans_start();
        $this->db->insert('colores_piel', array('Color' => $nombre));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Actualiza el nombre de un color
     * Devuelve:
     *  NULL: Si hubo error
     *  TRUE/FALSE: Si se pudo o no actualizar
     *  1: Si existe otro registro con el nombre nuevo
     */

    public function actualizar($idColor, $nombre) {
        $result = $this->db->query('
            SELECT idColor
            FROM colores_piel
            WHERE idColor <> ? and Color = ?', array($idColor, $nombre));

        if ($result === FALSE)
            return null;

        if ($result->num_rows() > 0)
            return 1;

        $this->db->trans_start();
        $this->db->update('colores_piel', array('Color' => $nombre), array('idColor' => $idColor));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Elimina un registro
     * Devuelve:
     *  NULL: Si hubo error
     *  TRUE/FALSE: Si se pudo o no eliminar
     */

    public function eliminar($idColor) {
        $this->db->trans_start();
        $this->db->delete('colores_piel', array('idColor' => $idColor));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }
    
    
    /*
     * Obtiene todos los registros
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array:
     *      Si $checkUse = false: La llave es el ID y el valor es el nombre.
     *      Si $checkUse = true: La llave es el ID y el valor es un array con las llaves: nombre y usado.
     */

    public function lista($checkUse = false) {
        $query = $this->db->query('
            SELECT
                c.idColor, c.Color' .
                ($checkUse ? ', case when exists(select * from estudiantes e where e.color_piel = c.idColor) then 1 else 0 end as usado ' : ' ') .
           'FROM colores_piel c
            ORDER BY c.Color');
        if ($query === FALSE)
            return null;

        $result = array();
        foreach ($query->result() as $row)
            $result[$row->idColor] = $checkUse ? array('nombre' => $row->Color, 'usado' => $row->usado) : $row->Color;

        return $result;
    }

}

?>