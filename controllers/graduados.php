<?php

class Graduados extends CI_Controller {

    private $imprimible = false;

    public function __construct() {
        parent::__construct();

        $this->load->model('estudiante');
    }

    public function index($idCurso = null, $ordenarXApellidos = true) {
        $cursos = $this->curso->lista(true);

        if (empty($cursos)) {
            $this->load->view($this->imprimible ? 'header_print' : 'header', array('titulo' => 'Listado de graduados'));
            if (is_null($cursos))
                $this->load->view('common/dberror');
            else
                $this->load->view('common/error', array('error' => 'No se ha obtenido suficientes datos para mostrar el formulario.'));
            $this->load->view($this->imprimible ? 'footer_print' : 'footer');
            return;
        }

        //Comprobar si existe el curso, si no es así, poner los implícitos
        if (!isset($cursos[$idCurso]))
            $idCurso = $this->curso->activo_id();

        //Obtener los estudiantes del curso activo
        $estudiantes = $this->estudiante->graduados($idCurso, $ordenarXApellidos);
        if (is_array($estudiantes))
            $datos = array(
                'estudiantes' => $estudiantes,
                'cursos' => $cursos,
                'idCurso' => $idCurso,
                'ordenarXApellidos' => $ordenarXApellidos,
                'imprimible' => $this->imprimible
            );
        else
            $datos = null;

        if ($this->imprimible)
            $this->load->view('header_print', array('titulo' => 'Listado de graduados del curso ' . $cursos[$idCurso]));
        else
            $this->load->view('header', array('titulo' => 'Listado de graduados'));

        if (is_null($datos))
            $this->load->view('common/dberror');
        else
            $this->load->view('graduados', array('datos' => $datos));

        $this->load->view($this->imprimible ? 'footer_print' : 'footer');
    }

    public function printable($idCurso = null, $ordenarXApellidos = true) {
        $this->imprimible = true;
        $this->index($idCurso, $ordenarXApellidos);
    }

}