<?php

class Grupo extends CI_Model {
    /*
     * Inserta un grupo
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  0: No se ha inicializado el curso siguiente
     *  1: Si el grupo ya existe
     *  True/False: Si se pudo o no agregar el grupo
     */

    public function insertar($grado, $nombre, $cursoActivo = true) {
        $idCurso = $cursoActivo ? $this->curso->activo_id() : $this->curso->siguiente_id();
        if (!$idCurso)
            return 0;

        $nombre = strtoupper($nombre);

        //Verificar si no existe otro grupo con el mismo nombre y grado en el curso indicado
        $res = $this->db->query('
          SELECT
            CASE WHEN EXISTS(SELECT * FROM grupos WHERE idcurso = ? and grado = ? and nombre = ?)
            THEN 1 ELSE 0 END as exist', array($idCurso, $grado, $nombre));

        if ($res === FALSE)
            return null;

        if ($res->row()->exist == 1)
            return 1; //Ya existe otro grupo con el nombre especificado

        $this->db->trans_start();
        $this->db->insert('grupos', array('idcurso' => $idCurso, 'grado' => $grado, 'nombre' => $nombre));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Actualiza el nombre y el grado de un grupo
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se pudo o no actualizar
     *  1: Si el grupo ya existe en el grado especificado
     */

    public function actualizar($idGrupo, $grado, $nombre, $cursoActivo = true) {
        $idCurso = $cursoActivo ? $this->curso->activo_id() : $this->curso->siguiente_id();
        if (!$idCurso)
            return 0;

        $nombre = strtoupper($nombre);

        //Verificar si no existe otro grupo con el mismo nombre y grado en el curso indicado
        $res = $this->db->query('
          SELECT
            CASE WHEN EXISTS(SELECT * FROM grupos WHERE idcurso = ? and grado = ? and nombre = ? and idgrupo <> ?)
            THEN 1 ELSE 0 END as exist', array($idCurso, $grado, $nombre, $idGrupo));

        if ($res === FALSE)
            return null;

        if ($res->row()->exist == 1)
            return 1; //Ya existe otro grupo con el nombre especificado

        $this->db->trans_start();
        $this->db->update('grupos', array('grado' => $grado, 'nombre' => $nombre), array('idgrupo' => $idGrupo));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Elimina un grupo. No puede haber estudiantes en el grupo
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  1: Si el grupo contiene estudiantes
     *  TRUE/FALSE: Si se pudo o no eliminar
     */

    public function eliminar($idGrupo) {
        $query = $this->db->query('select count(*) as tieneEstud from estudiantes_grupos where grupo = ?', $idGrupo);
        if ($query === false)
            return null;

        if ($query->row()->tieneEstud > 0)
            return 1; //El grupo contiene estudiantes

        $this->db->trans_start();
        $this->db->delete('grupos', array('idgrupo' => $idGrupo));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Obtiene los grupos del curso especificado
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Contiene los datos de los grupos. La llave es el ID y el valor es un array
     *         cuyas llaves son: grado, nombre y cantEstud.
     */

    public function lista($idCurso) {
        $query = $this->db->query('
            SELECT g.idgrupo, g.grado, g.nombre, (select count(*) from estudiantes_grupos eg where eg.grupo = g.idgrupo) as cantEstud
            FROM grupos g
            WHERE g.idcurso = ?
            ORDER BY g.grado, g.nombre', array($idCurso));

        if ($query === FALSE)
            return null;

        $result = array();
        foreach ($query->result() as $row)
            $result[$row->idgrupo] = array('grado' => $row->grado, 'nombre' => $row->nombre, 'cantEstud' => $row->cantEstud);

        return $result;
    }

}

?>