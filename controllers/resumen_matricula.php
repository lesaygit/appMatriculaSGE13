<?php

class Resumen_matricula extends CI_Controller {

    private $imprimible = false;

    public function __construct() {
        parent::__construct();

        $this->load->model('grupo');
        $this->load->model('estudiante');
    }

    public function index($idCurso = null) {
        $cursos = $this->curso->lista(true);
        if (empty($cursos)) {
            $this->load->view($this->imprimible ? 'header_print' : 'header', array('titulo' => 'Resumen de matrícula'));
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

        //Obtener los estudiantes del curso activo
        $resumen = $this->curso->resumen_matricula($idCurso);
        if (is_array($resumen))
            $datos = array(
                'idCurso' => $idCurso,
                'cursos' => $cursos,
                'resumen' => $resumen,
                'imprimible' => $this->imprimible
            );
        else
            $datos = null;

        $titulo = array('titulo' => "Resumen de matrícula del curso {$cursos[$idCurso]}");

        if ($this->imprimible)
            $this->load->view('header_print', $titulo);
        else
            $this->load->view('header', $titulo);

        if (is_null($datos))
            $this->load->view('common/dberror');
        else
            $this->load->view('resumen_matricula', array('datos' => $datos));

        $this->load->view($this->imprimible ? 'footer_print' : 'footer');
    }

    public function printable($idCurso = null) {
        $this->imprimible = true;
        $this->index($idCurso);
    }

}