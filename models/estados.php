<?php

define('EST_BAJA', 1);
define('EST_PROMOVIDO', 2);
define('EST_GRADUADO', 3);
define('EST_REPITENTE', 4);

class Estados extends CI_Model {
    /*
     * Obtiene la lista de todos los estados
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Contiene los registros obtenidos. La llave es el ID y el valor es el nombre.
     */

    public function lista() {
        $query = $this->db->query('SELECT idestado, nombre FROM estados');

        if ($query === FALSE)
            return null;

        $result = array();

        foreach ($query->result() as $row)
            $result[$row->idestado] = $row->nombre;

        return $result;
    }

}

?>