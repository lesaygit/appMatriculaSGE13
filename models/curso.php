<?php

class Curso extends CI_Model {
    /*
     * Agrega el curso siguiente al que está activo en la configuración
     * si no se ha agregado antes
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  FALSE: Si no se pudo insertar
     *  -1: El curso ya ha sido agregado con anterioridad
     *  int: Id del curso
     */

    public function insertar_siguiente() {
        $a_inicio = $this->activo_año();

        //Verificar si no se ha agregado aún el curso siguiente
        $res = $this->db->query('
            SELECT
                CASE WHEN EXISTS(select * from cursos where a_inicio = ?)
                THEN 1 ELSE 0 END as exist', $a_inicio + 1);

        if ($res === FALSE)
            return null;

        $row = $res->row();

        if ($row->exist == 1)
            return -1; //El curso ya ha sido agregado con anterioridad

        $this->db->trans_start();
        $this->db->insert('cursos', array('a_inicio' => $a_inicio + 1));
        $idCurso = $this->db->insert_id();
        $this->db->trans_complete();

        if ($this->db->trans_status())
            return $idCurso;

        return false;
    }

    /*
     * Obtiene el id y el año del curso activo
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Contiene los registros obtenidos. La llave es el ID y el valor es el año.
     */

    public function activo($asString = false) {
        $res = $this->db->query('select idcurso, a_inicio from cursos where idcurso = (select curso_activo from configuracion where id = 1)');
        if ($res === false)
            return $asString ? '' : null;

        if ($asString)
            return $res->row()->a_inicio . '-' . ($res->row()->a_inicio + 1);

        return array($res->row()->idcurso => $res->row()->a_inicio);
    }

    /*
     * Devuelve el curso activo como string. Los datos se toman de la sesión, que son actualizados siempre que se ejecuta una página.
     */

    public function activo_str() {
        $cursoActivo = $this->session_values->cursoActivo;
        return current($cursoActivo) . '-' . (current($cursoActivo) + 1);
    }

    /*
     * Devuelve el id del curso activo. Los datos se toman de la sesión, que son actualizados siempre que se ejecuta una página.
     */

    public function activo_id() {
        $cursoActivo = $this->session_values->cursoActivo;
        return key($cursoActivo);
    }

    /*
     * Devuelve el año del curso activo. Los datos se toman de la sesión, que son actualizados siempre que se ejecuta una página.
     */

    public function activo_año() {
        $cursoActivo = $this->session_values->cursoActivo;
        return current($cursoActivo);
    }

    /*
     * Obtiene el id y el año del curso siguiente al curso activo en la configuración
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  False: Si no existe tal curso
     *  Array: Contiene los registros obtenidos. La llave es el ID y el valor es el año.
     */

    public function siguiente() {
        $curso = $this->activo();
        if (!is_array($curso))
            return $curso;

        $a_inicio = current($curso);

        $res = $this->db->query('select idcurso, a_inicio from cursos where a_inicio = ?', $a_inicio + 1);
        if ($res === FALSE)
            return null;

        if ($res->num_rows() == 1)
            return array($res->row()->idcurso => $res->row()->a_inicio);

        return false;
    }

    /*
     * Devuelve el curso siguiente como string. Los datos se toman de la sesión, que son actualizados siempre que se ejecuta una página.
     */

    public function siguiente_str() {
        $cursoActivo = $this->activo_año();
        return ($cursoActivo + 1) . '-' . ($cursoActivo + 2);
    }

    /*
     * Devuelve el id del curso siguiente. Los datos se toman de la sesión, que son actualizados siempre que se ejecuta una página.
     */

    public function siguiente_id() {
        $curso = $this->session_values->cursoSiguiente;
        if ($curso === false)
            return false;

        return key($curso);
    }

    /*
     * Devuelve el año del curso siguiente. Los datos se toman de la sesión, que son actualizados siempre que se ejecuta una página.
     */

    public function siguiente_año() {
        return current($this->session_values->cursoActivo) + 1;
    }

    /*
     * Determina si es posible activar el curso siguiente. Para esto deben haber grupos en los tres grados, no puede
     * haber ninguno vacío, y todos los estudiantes deben haber sido procesados
     * Devuelve:
     *  Null: Si no fue posible determinar
     *  True/False: Si es activable o no
     */

    public function siguiente_es_activable() {
        //Debe haberse inicializado el curso siguiente
        if ($this->siguiente_id() === false)
            return false;

        $idCursoActivo = $this->activo_id();

        //Chequear en el curso activo si hay grupos en los 3 grados, si no hay grupos vacíos y si se le ha dado
        //tratamiento a cada estudiante
        $result = $this->db->query('
            select
                (select count(distinct grado) from grupos g where g.idcurso = ?) as cantGrados,
                (select count(*) from grupos g where g.idcurso = ? and not exists(select * from estudiantes_grupos eg where eg.grupo = g.idgrupo)) as gruposVacios,
                (select count(*)
                 from estudiantes_grupos eg
                   inner join grupos g on eg.grupo = g.idgrupo
                 where (estado is null) and (g.idcurso = ?)) as cantNoProcesados', array($idCursoActivo, $idCursoActivo, $idCursoActivo));

        if ($result === false)
            return null;

        $datos = $result->row();

        //return $datos->cantGrados == 3 && $datos->gruposVacios == 0 && $datos->cantNoProcesados == 0;
        
        //No se puede activar el curso siguiente si no existen grupos en los 3 grados, si hay algún grupo vacío
        //o si existe al menos un estudiante sin procesar
        if ($datos->cantGrados != 3 || $datos->gruposVacios > 0 || $datos->cantNoProcesados > 0)
            return false;

        return true;
    }

    /*
     * Establece el curso siguiente como curso activo en la configuración.
     * No se permite pasar al curso siguiente si no se ha definido un estado
     * para cada estudiante en el curso activo
     * Devuelve:
     *  NULL: Si no se pudo completar la operación producto a algún error
     *  0: Si no se permite activar el curso siguiente
     *  True/False: Si se pudo o no activar el cuso siguiente
     */

    public function activar_siguiente() {
        $esActivable = $this->siguiente_es_activable();

        if (is_null($esActivable))
            return null;

        if (!$esActivable)
            return 0;

        $this->db->trans_start();
        $this->db->update('configuracion', array('curso_activo' => $this->siguiente_id()));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Obtiene la lista de todos los cursos
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Contiene los registros obtenidos. La llave es el ID y el valor es el año de inicio del curso.
     */

    public function lista($asString = false) {
        $query = $this->db->query('SELECT idcurso, a_inicio FROM cursos ORDER BY a_inicio DESC');

        if ($query === FALSE)
            return null;

        $result = array();
        foreach ($query->result() as $row)
            $result[$row->idcurso] = $asString ? ($row->a_inicio . '-' . ($row->a_inicio + 1)) : $row->a_inicio;

        return $result;
    }

    /*
     * Obtiene un resumen de matrícula de un curso específico por grupos
     * Devuelve:
     *  NULL: Si hubo error
     *  Array: Tiene tres llaves: grado, nombre y sexo. El valor es la cantidad
     */

    public function resumen_matricula($idCurso) {
        $query = $this->db->query('
            select g.grado, g.nombre, e.sexo, count(*) as cant
            from estudiantes e
                inner join estudiantes_grupos eg on e.idestudiante = eg.idestudiante
                inner join grupos g on g.idgrupo = eg.grupo
            where g.idcurso = ?
            group by g.idgrupo
            order by g.grado, g.nombre', array($idCurso));

        if ($query === FALSE)
            return null;

        $result = array();
        foreach ($query->result() as $row)
            $result[$row->grado][$row->nombre][$row->sexo] = $row->cant;

        return $result;
    }

}

?>