<?php

class Estudiante extends CI_Model {
    /*
     * Inserta un nuevo estudiante en el grupo especificado. El Carnet de Identidad no puede
     * repetirse en el curso activo.
     * $datos debe ser un array con las llaves siguientes (todas en minúsculas):
     *      Obligatorias: CI,nombre,apellido1,apellido2,localidad,sexo
     *      Opcionales: direccion,color_ojos,color_pelo,color_piel,talla,peso
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se pudo o no insertar
     *  1: Si el CI ya existe
     */

    public function matricular($idGrupo, $datos) {
        $cursoActivo = $this->curso->activo_id();

        try {
            //Verificar si existe otro estudiante con el mismo CI en el curso activo
            $res = $this->db->query('
            SELECT
                CASE WHEN EXISTS(select * from grupos where idcurso = ?) THEN 1 ELSE 0 END as delCursoActivo,
                CASE WHEN EXISTS(
                    SELECT *
                    FROM estudiantes e
                        INNER JOIN estudiantes_grupos eg ON eg.idestudiante = e.idestudiante
                        INNER JOIN grupos g ON g.idgrupo = eg.grupo
                    WHERE e.CI = ? and g.idcurso = ?)
                THEN 1 ELSE 0 END as exist', array($cursoActivo, $datos['CI'], $cursoActivo));

            if ($res === FALSE)
                return null;

            if (!$res->row()->delCursoActivo)
                return null; //Posible trampa, se está agregando a un curso que no es el activo

            if ($res->row()->exist == 1)
                return 1; //Ya existe otro CI igual

            $data = array();
            foreach (explode(',', 'CI,nombre,apellido1,apellido2,localidad,sexo') as $field)
                $data[$field] = $datos[$field];
            foreach (explode(',', 'direccion,color_ojos,color_pelo,color_piel,talla,peso') as $field)
                if (isset($datos[$field]))
                    $data[$field] = $datos[$field];
        } catch (Exception $e) {
            return null;
        }

        $this->db->trans_start();
        $this->db->insert('estudiantes', $data);
        $idEstudiante = $this->db->insert_id();
        $this->db->insert('estudiantes_grupos', array('idestudiante' => $idEstudiante, 'grupo' => $idGrupo));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Actualiza los datos de un estudiante. El Carnet de Identidad no puede repetirse en los últimos
     * 3 cursos, incluyendo el curso activo.
     * $datos debe ser un array con las llaves siguientes (todas en minúsculas):
     *      Obligatorias: CI,nombre,apellido1,apellido2,localidad,sexo
     *      Opcionales: direccion,color_ojos,color_pelo,color_piel,talla,peso
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se pudo o no actualizar
     *  1: Si el CI ya existe
     */

    public function actualizar($idEstudiante, $datos) {
        $cursoActivo = $this->curso->activo_id();

        try {
            //Verificar si existe otro estudiante con el mismo CI en los últimos 3 cursos (incluyendo el activo)
            $res = $this->db->query('
            SELECT
                CASE WHEN EXISTS(
                    SELECT *
                    FROM estudiantes e
                        INNER JOIN estudiantes_grupos eg ON eg.idestudiante = e.idestudiante
                        INNER JOIN grupos g ON g.idgrupo = eg.grupo
                    WHERE e.CI = ? and e.idestudiante <> ? and g.idcurso = ?)
                THEN 1 ELSE 0 END as exist', array($datos['CI'], $idEstudiante, $cursoActivo));

            if ($res === FALSE)
                return null;

            if ($res->row()->exist == 1)
                return 1; //Ya existe otra localidad con el nombre especificado

            $data = array();
            foreach (explode(',', 'CI,nombre,apellido1,apellido2,localidad,sexo') as $field)
                $data[$field] = $datos[$field];
            foreach (explode(',', 'direccion,color_ojos,color_pelo,color_piel,talla,peso') as $field)
                if (isset($datos[$field]) || is_null($datos[$field]))
                    $data[$field] = $datos[$field];
        } catch (Exception $e) {
            return null;
        }

        $this->db->trans_start();
        $this->db->update('estudiantes', $data, array('idestudiante' => $idEstudiante));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Cambia un estudiante hacia otro grupo del curso activo y del mismo año.
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  -1: Si no existe el estudiante en el curso activo
     *  0: Si el grupo no existe en el curso activo y en el mismo año
     *  TRUE/FALSE: Si se tuvo éxito o no
     */

    public function cambiar_grupo($idEstudiante, $idGrupo) {
        $cursoActivo = $this->curso->activo_id();

        $res = $this->db->query('
            SELECT eg.grupo, case when g.grado = (select gp.grado from grupos gp where gp.idgrupo = ?) then 1 else 0 end as mismoGrado
            FROM estudiantes_grupos eg
                INNER JOIN grupos g ON g.idgrupo = eg.grupo
            WHERE eg.idestudiante = ? and g.idcurso = ? and (estado is null)', array($idGrupo, $idEstudiante, $cursoActivo));

        if ($res === false)
            return null;

        if ($res->num_rows() == 0)
            return -1;

        if (!$res->row()->mismoGrado)
            return 0;

        $this->db->trans_start();
        $this->db->update('estudiantes_grupos', array('grupo' => $idGrupo), array('idestudiante' => $idEstudiante, 'grupo' => $res->row()->grupo));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Eliminar un estudiante. No se permite eliminar el estudiante si ya tiene un estado (esto implica que
     * no puede tener información de cursos anteriores o que se haya procesado para pasar al curso siguiente)
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  TRUE/FALSE: Si se tuvo éxito o no
     *  1: Si el estudiante ya tiene al menos un estado
     */

    public function eliminar($idEstudiante) {
        $res = $this->db->query('
            SELECT
                CASE WHEN EXISTS(SELECT * FROM estudiantes_grupos WHERE idestudiante = ? and not (estado is null))
                THEN 1 ELSE 0 END as exist', array($idEstudiante));

        if ($res === false)
            return null;

        if ($res->row()->exist)
            return 1;

        $this->db->trans_start();
        $this->db->delete('estudiantes', array('idestudiante' => $idEstudiante));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Da baja a un estudiante en el curso activo. No permite dar baja si el estudiante ya tiene otro estado
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  0: No existe el estudiante o tiene otro estado
     *  TRUE/FALSE: Si se tuvo éxito o no
     */

    public function baja($idEstudiante, $motivo) {
        $cursoActivo = $this->curso->activo_id();

        $res = $this->db->query('
            SELECT eg.grupo
            FROM estudiantes_grupos eg
                INNER JOIN grupos g ON g.idgrupo = eg.grupo
            WHERE eg.idestudiante = ? and g.idcurso = ? and (estado is null)', array($idEstudiante, $cursoActivo));

        if ($res === false)
            return null;

        if ($res->num_rows() == 0)
            return 0;

        $this->db->trans_start();
        $this->db->update('estudiantes_grupos', array('estado' => 1, 'obs_baja' => $motivo), array('idestudiante' => $idEstudiante, 'grupo' => $res->row()->grupo));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Limpia el estado de un estudiante que está marcado como baja en el curso activo
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  0: No existe el estudiante o no está marcado como baja en el curso activo
     *  TRUE/FALSE: Si se tuvo éxito o no
     */

    public function deshacer_baja($idEstudiante) {
        $cursoActivo = $this->curso->activo_id();

        $res = $this->db->query('
            SELECT eg.grupo
            FROM estudiantes_grupos eg
                INNER JOIN grupos g ON g.idgrupo = eg.grupo
            WHERE eg.idestudiante = ? and g.idcurso = ? and estado = 1', array($idEstudiante, $cursoActivo));

        if ($res === false)
            return null;

        if ($res->num_rows() == 0)
            return 0;

        $this->db->trans_start();
        $this->db->update('estudiantes_grupos', array('estado' => null, 'obs_baja' => null), array('idestudiante' => $idEstudiante, 'grupo' => $res->row()->grupo));
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Obtiene todos los estudiantes de un grupo
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Contiene los datos del estudiante. La llave es el ID y el valor un array.
     */

    public function datos($idEstudiante) {
        $datos = $this->db->query('
            SELECT e.CI, e.nombre, e.apellido1, e.apellido2, e.direccion, e.talla, e.peso, e.sexo,
                   co.Color as color_ojos, cpi.Color as color_piel, cpe.Color as color_pelo,
                   l.Nombre as localidad, eg.estado
            FROM estudiantes e
                 INNER JOIN estudiantes_grupos eg ON eg.idestudiante = e.idestudiante
                 INNER JOIN grupos g ON g.idgrupo = eg.grupo
                 LEFT OUTER JOIN colores_ojos co ON co.idColor = e.color_ojos
                 LEFT OUTER JOIN colores_piel cpi ON cpi.idColor = e.color_piel
                 LEFT OUTER JOIN colores_pelo cpe ON cpe.idColor = e.color_pelo
                 LEFT OUTER JOIN localidades l ON l.idLocalidad = e.localidad
            WHERE e.idestudiante = ?', array($idEstudiante));

        $datosCursos = $this->db->query('
            SELECT concat(g.grado, \'-\', g.nombre) as grupo, concat(c.a_inicio, \'-\', c.a_inicio + 1) as curso, eg.estado as idestado, es.nombre as estado, eg.obs_baja
            FROM estudiantes e
                 INNER JOIN estudiantes_grupos eg ON eg.idestudiante = e.idestudiante
                 INNER JOIN grupos g ON g.idgrupo = eg.grupo
                 INNER JOIN cursos c ON c.idcurso = g.idcurso
                 LEFT OUTER JOIN estados es ON es.idestado = eg.estado
            WHERE e.idestudiante = ?
            ORDER BY c.a_inicio', array($idEstudiante));

        if ($datos === FALSE || $datosCursos === FALSE)
            return null;

        $result = $datos->row_array();

        $cursos = array();
        foreach ($datosCursos->result_array() as $row)
            $cursos[] = $row;

        $result['cursos'] = $cursos;

        return $result;
    }

    /*
     * Obtiene todos los estudiantes que coinciden con los datos suministrados
     * $campo puede ser uno de los siguientes valores
     *      1 => Buscar en el Carnet de identidad
     *      2 => Buscar en el nombre
     *      3 => Buscar en el primer apellido
     *      4 => Buscar en el segundo apellido
     *      5 => Buscar en los apellidos
     *      4 => Buscar en el nombre y en los apellidos
     * $patron puede ser uno de los siguientes valores
     *      0 => igual a...
     *      1 => que comience con...
     *      2 => que contenga...
     *      3 => que termine con...
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Contiene los datos de los estudiantes encontrados. La llave es
     *         el ID y el valor un array.
     */

    public function buscar($campo, $texto, $cursoActivo = true, $patron = 0) {
        if (is_null($texto) || strlen(trim($texto)) == 0)
            return array();

        $texto = str_replace(array("'", '%'), array("''", '%%'), $texto);

        switch ($patron) {
            case 1: //Que comience con...
                $pattern = " like '%s%%'";
                break;

            case 2: //Que contenga...
                $pattern = " like '%%%s%%'";
                break;

            case 3: //Que termine con...
                $pattern = " like '%%%s'";
                break;

            case 0: //Igual a
            default:
                $pattern = " = '%s'";
                break;
        }

        switch ($campo) {
            case 1: //CI
                $condition = "(e.CI {$pattern})";
                break;
            case 2: //Nombre
                $condition = "(e.nombre {$pattern})";
                break;

            case 3: //Primer apellido
                $condition = "(e.apellido1 {$pattern})";
                break;

            case 4: //Segundo apellido
                $condition = "(e.apellido2 {$pattern})";
                break;

            case 5: //Apellidos
                $condition = "(e.apellido1 {$pattern} OR e.apellido2 {$pattern})";
                break;

            case 6: //Nombre completo
                $condition = "(e.nombre {$pattern} OR e.apellido1 {$pattern} OR e.apellido2 {$pattern})";
                break;

            default:
                return array();
        }

        //Sustituir los %s por el texto
        $condition = sprintf($condition, $texto, $texto, $texto);

        if ($cursoActivo === true)
            $condition = '(g.idcurso = ' . $this->curso->activo_id() . ') AND ' . $condition;

        $datos = $this->db->query('
            SELECT e.idestudiante, e.CI, e.nombre, e.apellido1, e.apellido2, e.sexo
            FROM estudiantes e
                 INNER JOIN estudiantes_grupos eg ON eg.idestudiante = e.idestudiante
                 INNER JOIN grupos g ON g.idgrupo = eg.grupo
                 LEFT OUTER JOIN localidades l ON l.idLocalidad = e.localidad
            WHERE ' . $condition . '
            ORDER BY e.nombre, e.apellido1, e.apellido2');

        if ($datos === FALSE)
            return null;

        $result = array();
        foreach ($datos->result_array() as $row)
            $result[$row['idestudiante']] = $row;

        return $result;
    }

    /*
     * Obtiene todos los estudiantes de un grupo
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Contiene los datos del estudiante. La llave es el ID y el valor un array.
     */

    public function lista($idGrupo, $ordenarXApellido = false) {
        $query = $this->db->query('
            SELECT e.idestudiante, e.CI, e.nombre, e.apellido1, e.apellido2, e.direccion, e.talla, e.peso, e.sexo,
                   co.idColor as color_ojos, cpi.idColor as color_piel, cpe.idColor as color_pelo, l.idLocalidad as localidad,
                   g.grado as idgrado, eg.grupo as idgrupo, g.nombre as nombreGrupo, g.idcurso, eg.estado,
                   eg.obs_baja
            FROM estudiantes e
                 INNER JOIN estudiantes_grupos eg ON eg.idestudiante = e.idestudiante
                 INNER JOIN grupos g ON g.idgrupo = eg.grupo
                 LEFT OUTER JOIN colores_ojos co ON co.idColor = e.color_ojos
                 LEFT OUTER JOIN colores_piel cpi ON cpi.idColor = e.color_piel
                 LEFT OUTER JOIN colores_pelo cpe ON cpe.idColor = e.color_pelo
                 LEFT OUTER JOIN localidades l ON l.idLocalidad = e.localidad
            WHERE g.idgrupo = ?
            ORDER BY ' . ($ordenarXApellido ? 'e.apellido1, e.apellido2, e.nombre' : 'e.nombre, e.apellido1, e.apellido2'), array($idGrupo));

        if ($query === FALSE)
            return null;

        $result = array();

        foreach ($query->result_array() as $row)
            $result[$row['idestudiante']] = $row;

        return $result;
    }
    
    /*
     * Obtiene todos los estudiantes de un curso
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Contiene los datos de los estudiantes. La llave es el ID y el valor un array.
     */

    public function lista_curso($idCurso) {
        $query = $this->db->query('
            SELECT e.idestudiante, e.CI, e.nombre, e.apellido1, e.apellido2, e.sexo, eg.estado,
                    eg.obs_baja
            FROM estudiantes e
                 INNER JOIN estudiantes_grupos eg ON eg.idestudiante = e.idestudiante
                 INNER JOIN grupos g ON g.idgrupo = eg.grupo
            WHERE g.idcurso = ?
            ORDER BY e.nombre, e.apellido1, e.apellido2', array($idCurso));

        if ($query === FALSE)
            return null;

        $result = array();

        foreach ($query->result_array() as $row)
            $result[$row['idestudiante']] = $row;

        return $result;
    }

    /*
     * Obtiene un resumen según los colores de piel, pelo y ojos de un curso
     * específico
     * Devuelve:
     *  NULL: Si hubo error
     *  Array: Tiene tres llaves: color, grado y grupo. El valor la cantidad
     */

    public function resumen_color($idCurso, $colorDe) {
        if (!in_array($colorDe, array('ojos', 'piel', 'pelo')))
            return null;

        $query = $this->db->query('
            SELECT g.grado, g.nombre as grupo, co.color,
                (SELECT count(*)
                 FROM estudiantes e
                    INNER JOIN estudiantes_grupos eg on eg.idestudiante = e.idestudiante
                 WHERE e.color_' . $colorDe . ' = co.idColor and eg.grupo = g.idgrupo) as cant
            FROM grupos g CROSS JOIN colores_' . $colorDe . ' co
            WHERE g.idcurso = ?
            ORDER BY co.color, g.grado, grupo', array($idCurso));

        if ($query === FALSE)
            return null;

        $result = array();
        foreach ($query->result() as $row)
            $result[$row->color][$row->grado][$row->grupo] = $row->cant;

        return $result;
    }

    /*
     * Obtiene los grupos de todos estudiantes del curso siguiente
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  False: Si no existe el curso siguiente
     *  Array: Contiene los datos del estudiante. La llave es el ID y el valor es el Id del grupo.
     */

    public function grupos_curso_siguiente() {
        $idCurso = $this->curso->siguiente_id();
        if (!$idCurso)
            return FALSE;

        $query = $this->db->query('
            SELECT eg.idestudiante, eg.grupo
            FROM estudiantes_grupos eg
                 INNER JOIN grupos g ON g.idgrupo = eg.grupo
            WHERE g.idcurso = ?', array($idCurso));

        if ($query === FALSE)
            return null;

        $result = array();
        foreach ($query->result() as $row)
            $result[$row->idestudiante] = $row->grupo;

        return $result;
    }

    /*
     * Procesa un lote de estudiantes.
     * $estados debe tener como llaves los Id de los estudiantes y como valor el
     * estado.
     * $grupos debe tener como llaves los Id de los estudiantes y como valor el
     * Id del grupo de destino.
     * Se chequea que el grupo de destino sea del curso siguiente y que el grado
     * del grupo sea compatible con el estado.
     * 
     * Devuelve:
     *  NULL: Si no se pudo completar la operación
     *  0: Incongruencia en los datos
     *  TRUE/FALSE: Si se tuvo éxito o no
     */

    public function procesar_lote($estados, $grupos) {
        if (!(is_array($estados) && is_array($grupos)))
            return false;

        $this->load->model('estados');

        $cursoActivo = $this->curso->activo_id();
        $cursoSiguiente = $this->curso->siguiente_id();

        if (!$cursoSiguiente)
            return false;

        foreach ($estados as $idEstud => $estado) {
            //No se permite dar baja por aquí
            if ($estado == EST_BAJA)
                return 0;

            $requiereGrupo = $estado == EST_PROMOVIDO || $estado == EST_REPITENTE;
            if ($requiereGrupo) {
                if (empty($grupos[$idEstud]))
                    return 0;

                $grupoDest = $grupos[$idEstud];
                $gradoSuma = $estado == EST_PROMOVIDO ? 1 : 0;

                $case = '
                    CASE WHEN EXISTS(
                        select *
                        from grupos
                        where idgrupo = ? and idcurso = ? and grado = ? + (
                            SELECT g.grado
                            FROM grupos g
                            INNER JOIN estudiantes_grupos eg ON g.idgrupo = eg.grupo
                            WHERE eg.idestudiante = ? and g.idcurso = ?))
                    THEN 1 ELSE 0 END as grupoOk';
            }
            else {
                $gradoSuma = $grupoDest = 0;
                $case = '1 as grupoOk';
                unset($grupos[$idEstud]);
            }

            //Chequear que el estudiante se encuentre en el curso activo
            $res = $this->db->query("
                SELECT
                    CASE WHEN EXISTS(
                        SELECT eg.grupo
                        FROM estudiantes_grupos eg
                            INNER JOIN grupos g ON g.idgrupo = eg.grupo
                        WHERE eg.idestudiante = ? and g.idcurso = ?)
                    THEN 1 ELSE 0 END as estudOk,
                    $case", array($idEstud, $cursoActivo, $grupoDest, $cursoSiguiente, $gradoSuma, $idEstud, $cursoActivo));

            if ($res === false)
                return null;

            if (!($res->row()->estudOk && $res->row()->grupoOk))
                return 0;
        }

        $this->db->trans_start();
        foreach ($estados as $idEstud => $estado) {
            //Eliminar cualquier información guardada para el curso siguiente
            $this->db->delete('estudiantes_grupos', "idestudiante = {$idEstud} and (select idcurso from grupos g where g.idgrupo = estudiantes_grupos.grupo) = {$cursoSiguiente}");

            //Actualizar la información del curso activo
            $this->db->update('estudiantes_grupos', array('estado' => $estado ? $estado : null), "idestudiante = {$idEstud} and (select idcurso from grupos g where g.idgrupo = estudiantes_grupos.grupo) = {$cursoActivo}");

            if (isset($grupos[$idEstud])) {
                //Agregar la información para el curso siguiente
                $this->db->insert('estudiantes_grupos', array('idestudiante' => $idEstud, 'grupo' => $grupos[$idEstud]));
            }
        }
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Obtiene los estudiantes graduados de un curso específico
     */

    public function graduados($idCurso, $ordenarXApellido = false) {
        $query = $this->db->query('
            SELECT e.idestudiante, e.CI, e.nombre, e.apellido1, e.apellido2, e.direccion, e.talla, e.peso, e.sexo,
                   co.idColor as color_ojos, cpi.idColor as color_piel, cpe.idColor as color_pelo, l.Nombre as localidad,
                   g.grado as idgrado, eg.grupo as idgrupo, g.nombre as nombreGrupo, g.idcurso, eg.estado
            FROM estudiantes e
                 INNER JOIN estudiantes_grupos eg ON eg.idestudiante = e.idestudiante
                 INNER JOIN grupos g ON g.idgrupo = eg.grupo
                 LEFT OUTER JOIN colores_ojos co ON co.idColor = e.color_ojos
                 LEFT OUTER JOIN colores_piel cpi ON cpi.idColor = e.color_piel
                 LEFT OUTER JOIN colores_pelo cpe ON cpe.idColor = e.color_pelo
                 LEFT OUTER JOIN localidades l ON l.idLocalidad = e.localidad
            WHERE g.idcurso = ? and eg.estado = 3
            ORDER BY ' . ($ordenarXApellido ? 'e.apellido1, e.apellido2, e.nombre' : 'e.nombre, e.apellido1, e.apellido2'), array($idCurso));

        if ($query === FALSE)
            return null;

        $result = array();

        foreach ($query->result_array() as $row)
            $result[$row['idestudiante']] = $row;

        return $result;
    }

}

?>