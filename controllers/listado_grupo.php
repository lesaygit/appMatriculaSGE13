<?php

class Listado_grupo extends CI_Controller {

    private $imprimible = false;

    public function __construct() {
        parent::__construct();

        $this->load->model('grupo');
        $this->load->model('estudiante');
    }

    public function index($idCurso = null, $idGrado = null, $idGrupo = null, $mostrarBajas = false, $ordenarXApellidos = true) {
        $cursos = $this->curso->lista(true);
        if (empty($cursos)) {
            $this->load->view($this->imprimible ? 'header_print' : 'header', array('titulo' => 'Listado de estudiantes'));
            if (is_null($cursos))
                $this->load->view('common/dberror');
            else
                $this->load->view('common/error', array('error' => 'No se ha obtenido suficientes datos para mostrar el formulario.'));
            $this->load->view($this->imprimible ? 'footer_print' : 'footer');
            return;
        }

        //Comprobar si existe el curso
        if (!isset($cursos[$idCurso]))
            $idCurso = $this->curso->activo_id();
        
        //Obtener los grupos del curso seleccionado
        $grupos = $this->grupo->lista($idCurso);
        if (empty($grupos)) {
            $this->load->view($this->imprimible ? 'header_print' : 'header', array('titulo' => 'Listado de estudiantes'));
            if (is_null($grupos))
                $this->load->view('common/dberror');
            elseif (!$grupos)
                $this->load->view('common/error', array('error' => 'No se ha definido grupos en el curso seleccionado.'));
            else
                $this->load->view('common/error', array('error' => 'No se ha obtenido suficientes datos para mostrar el formulario.'));
            $this->load->view($this->imprimible ? 'footer_print' : 'footer');
            return;
        }

        //Comprobar si existe el grupo en el grado especificado, si no es así, poner los implícitos
        if (!isset($grupos[$idGrupo]) || $grupos[$idGrupo]['grado'] != $idGrado) {
            $idGrupo = key($grupos);
            $idGrado = $grupos[$idGrupo]['grado'];
        }

        //Obtener los estudiantes del curso activo
        $estudiantes = $this->estudiante->lista($idGrupo, $ordenarXApellidos);
        if (is_array($estudiantes))
            $datos = array(
                'idCurso' => $idCurso,
                'idGrupo' => $idGrupo,
                'idGrado' => $idGrado,
                'cursos' => $cursos,
                'grupos' => $grupos,
                'estudiantes' => $estudiantes,
                'mostrarBajas' => $mostrarBajas,
                'ordenarXApellidos' => $ordenarXApellidos,
                'imprimible' => $this->imprimible
            );
        else
            $datos = null;

        if ($this->imprimible)
            $this->load->view('header_print', array('titulo' => 'Listado de estudiantes del grupo ' . $idGrado . '-' . $grupos[$idGrupo]['nombre']));
        else
            $this->load->view('header', array('titulo' => 'Listado de estudiantes'));

        if (is_null($datos))
            $this->load->view('common/dberror');
        else {
            $this->load->view('js/actualizarGrupos', array('grupos' => $grupos, 'idGrado' => $idGrado, 'idGrupo' => $idGrupo));
            $this->load->view('listado_grupo', array('datos' => $datos));
        }

        $this->load->view($this->imprimible ? 'footer_print' : 'footer');
    }

    public function printable($idGrado = null, $idGrupo = null, $mostrarBajas = false, $ordenarXApellidos = true) {
        $this->imprimible = true;
        $this->index($idGrado, $idGrupo, $mostrarBajas, $ordenarXApellidos);
    }

}