<?php

class Common_model extends CI_Model {

    /*
     * Devuelve los nombres de los posibles temas a seleccionar
     */
    public function temas() {
        return array(
            'default',
            'cosmo',
            'simplex',
            'spacelab',
            'spruce',
            'united',
        );
    }

    public function menu() {
        if ($this->session->userdata('isAdmin')) {
            $menu = array(
                'Administrar' =>
                array(
                    'Roles' => 'roles',
                    'Usuarios' => 'usuarios',
                ),
                'Clasificadores' =>
                array(
                    'Localidades' => 'localidades',
                    'Colores de ojos' => 'colores_ojos',
                    'Colores de piel' => 'colores_piel',
                    'Colores de pelo' => 'colores_pelo',
                ),
                'Auditoría' =>
                array(
                    'Trazas de usuarios' => 'view_log',
                ),
            );
        } else {
            $cursoActivo = 'Curso ' . $this->curso->activo_str() . ' (activo)';
            $cursoSigte = 'Curso ' . $this->curso->siguiente_str() . ' (siguiente)';

            $fullMenu = array(
                'Informes' => array(
                    'Listado por cursos' => 'listado_curso',
                    'Listado por grupos' => 'listado_grupo',
                    'Graduados por curso' => 'graduados',
                    'Buscar estudiante' => 'buscar',
                    'Resumen por color' => 'resumen_color',
                    'Resumen de matrícula' => 'resumen_matricula',
                ),
                $cursoActivo => array(
                    'Definir grupos' => 'grupos',
                    'Gestionar estudiantes' => 'estudiantes',
                ),
                $cursoSigte => array(
                    'Inicializar curso' => 'init_curso',
                    'Definir grupos' => 'grupos_cs',
                    'Procesar estudiantes' => 'procesar',
                    'Establecer como activo' => 'activar_curso',
                )
            );

            $cSigte = $this->curso->siguiente_id();
            $siguiente_activable = $this->curso->siguiente_es_activable();

            $menu = array();
            foreach ($fullMenu as $section => $items) {
                if (is_array($items)) {
                    $sectionItems = array();
                    foreach ($items as $title => $controller) {
                        if ($this->permisos->puede_ejecutar($controller)) {
                            if ($section == $cursoSigte) { //En la sección del curso siguiente, mostrar las opciones que hagan falta
                                if ($cSigte) {
                                    if ($controller == 'init_curso')
                                        $title = '*' . $title; //continue;
                                    if ($controller == 'activar_curso' && !$siguiente_activable)
                                        $title = '*' . $title; //continue;
                                } elseif ($controller != 'init_curso')
                                    $title = '*' . $title; //continue;
                            }
                            $sectionItems[$title] = $controller;
                        } else {
                            //Comentar esta línea si se desea ocultar la opción si no tiene permisos
                            $sectionItems['*' . $title] = $controller;
                        }
                    }
                    //if ($sectionItems) //Quitar comentario si se quiere ocultar las opciones deshabilitadas
                    $menu[$section] = $sectionItems;
                }// else {
                //$menu[$section] = $items;
                //}
            }
        }

        $menu['Usuario'] = array(
            'Cambiar contraseña' => 'change_pw',
            'Cambiar apariencia' => 'tema',
            'Cerrar sesión' => 'logout',
        );

        return $menu;
    }

    /*
     * Determina si existe la configuración
     */

    public function config_exists() {
        $query = $this->db->query('SELECT CASE WHEN EXISTS(SELECT * FROM configuracion WHERE id = 1) THEN 1 ELSE 0 END as exist');

        return $query === FALSE ? null : $query->row()->exist == 1;
    }

    /*
     * Obtiene los datos de la configuración.
     * Devuelve:
     *  NULL: Si ocurrió un error
     *  Array: Conteniendo los datos obtenidos. Las llaves son: centro, direccion, localidad
     */

    public function config_datos() {
        $query = $this->db->query('
            SELECT c.nombre_centro as centro, c.direccion, l.Nombre as localidad
            FROM configuracion c
                INNER JOIN localidades l ON l.idlocalidad = c.localidad
            WHERE id = 1');

        return $query === FALSE ? null : ($query->num_rows() == 1 ? $query->row_array() : array());
    }

    /*
     * Devuelve todas las provincias. NULL si no se pudo obtener los datos
     */

    public function get_provincias() {
        $query = $this->db->query('SELECT idProvincia, Provincia FROM provincias');
        if ($query === FALSE)
            return null;

        $provs = array();
        foreach ($query->result() as $row)
            $provs[$row->idProvincia] = $row->Provincia;

        return $provs;
    }

    /*
     * Devuelve los municipios de la provincia especificada. NULL si no se pudo obtener los datos
     */

    public function get_municipios($idProv) {
        $query = $this->db->query('SELECT idMunicipio, Municipio FROM municipios WHERE idProvincia = ?', $idProv);
        if ($query === FALSE)
            return null;

        $muns = array();
        foreach ($query->result() as $row)
            $muns[$row->idMunicipio] = $row->Municipio;

        return $muns;
    }

    /*
     * Devuelve todos los municipios por provincia. NULL si no se pudo obtener los datos
     */

    public function get_municipios_prov() {
        $query = $this->db->query('SELECT idMunicipio, Municipio, idProvincia FROM municipios ORDER BY idProvincia, idMunicipio');
        if ($query === FALSE)
            return null;

        $muns = array();
        foreach ($query->result() as $row)
            @$muns[$row->idProvincia][] = array($row->idMunicipio, $row->Municipio);

        return $muns;
    }

    /*
     * Devuelve las localidades del municipio especificado. NULL si no se pudo obtener los datos
     */

    public function get_localidades($idMun) {
        $locs = array();

        $query = $this->db->query('SELECT idLocalidad, Nombre, CodPostal FROM localidades WHERE idMunicipio = ?', $idMun);
        if ($query === FALSE)
            return null;

        foreach ($query->result() as $row)
            $locs[$row->idLocalidad] = array('nombre' => $row->Nombre, 'codigo' => $row->CodPostal);

        return $locs;
    }

    /*
     * Crea la configuración.
     * Devuelve:
     *  NULL: Si ocurre un error en la base de datos al verificar si ya existen datos
     *  1:    Si ya existen datos
     *  TRUE: Si se logra insertar la configuracion
     *  FALSE:Si no es posible
     */

    public function create_config($anoInicio, $idMunicipio, $localidad, $admNombre, $admApellidos, $admPw, $nombreCentro, $dirCentro) {
        //Verificar si ya existen datos
        $result = $this->db->query('
            select
                case when
                    exists(select * from usuarios) or
                    exists(select * from roles) or
                    exists(select * from cursos) or
                    exists(select * from localidades) or
                    exists(select * from configuracion)
                then 1 else 0 end as exist');

        if ($result === FALSE)
            return null; //Error en la base de datos

        if ($result->row()->exist == 1)
            return 1; //Ya existen datos en la base de datos

        $this->db->trans_start();

        //Insertar el curso inicial y obtener el id generado
        $this->db->insert('cursos', array('a_inicio' => $anoInicio));
        $idCurso = $this->db->insert_id();

        //Insertar la localidad y obtener su id
        $this->db->insert('localidades', array('idMunicipio' => $idMunicipio, 'Nombre' => $localidad));
        $idLocalidad = $this->db->insert_id();

        //Crear el usuario administrador
        $this->db->insert('usuarios', array('idusuario' => 1, 'login' => 'admin', 'nombre' => $admNombre, 'apellidos' => $admApellidos, 'password' => $admPw));

        //Crear el rol de administrador
        $this->db->insert('roles', array('idrol' => 1, 'nombre' => 'Administrador'));

        //Asignar el rol de administrador al usuario creado
        $this->db->insert('usuarios_roles', array('idusuario' => 1, 'idrol' => 1));

        //Crear la configuración
        $this->db->insert('configuracion', array('id' => 1, 'nombre_centro' => $nombreCentro, 'direccion' => $dirCentro, 'localidad' => $idLocalidad, 'curso_activo' => $idCurso));

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /*
     * Devuelve un array con la configuración del paginador para los datos proporcionados
     */

    public function get_paginator_config($cant, $url, $segment, $perPage, $page, &$offset) {
        $maxPage = ceil($cant / $perPage) - 1;
        if ($maxPage < 0)
            $maxPage = 0;

        if (!is_numeric($page))
            $page = 0;
        else {
            $page = (int) $page - 1;

            if ($page < 0)
                $page = 0;
            else
            if ($page > $maxPage)
                $page = $maxPage;
        }

        $offset = $page * $perPage;

        return array(
            'total_rows' => $cant,
            'cur_page' => $page,
            'per_page' => $perPage,
            'base_url' => base_url() . 'index.php/' . $url,
            'uri_segment' => $segment,
            'num_links' => 4,
            'use_page_numbers' => TRUE,
            'full_tag_open' => "<center><div id=\"paginator\" class=\"pagination\"><ul>",
            'full_tag_close' => "</ul></div></center>",
            'first_link' => '|&laquo;',
            'first_tag_open' => '<li>',
            'first_tag_close' => '</li>',
            'prev_link' => '&laquo;',
            'prev_tag_open' => '<li>',
            'prev_tag_close' => '</li>',
            'cur_tag_open' => '<li><span><strong>',
            'cur_tag_close' => '</strong></span></li>',
            'num_tag_open' => '<li>',
            'num_tag_close' => '</li>',
            'next_link' => '&raquo;',
            'next_tag_open' => '<li>',
            'next_tag_close' => '</li>',
            'last_link' => '&raquo;|',
            'last_tag_open' => '<li>',
            'last_tag_close' => '</li>',
            'anchor_class' => 'class="my_pager"'
        );
    }

}

?>
