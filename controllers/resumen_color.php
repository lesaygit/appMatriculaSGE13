<?php

class Resumen_color extends CI_Controller {

    private $imprimible = false;

    public function __construct() {
        parent::__construct();

        $this->load->model('grupo');
        $this->load->model('estudiante');
    }

    public function index($idCurso = null, $colorDe = 'piel') {
        $cursos = $this->curso->lista(true);
        if (empty($cursos)) {
            $this->load->view($this->imprimible ? 'header_print' : 'header', array('titulo' => 'Resumen de color'));
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

        if (!in_array($colorDe = strtolower($colorDe), array('ojos', 'piel', 'pelo')))
            $colorDe = 'piel';

        //Obtener los estudiantes del curso activo
        $resumen = $this->estudiante->resumen_color($idCurso, $colorDe);
        if (is_array($resumen))
            $datos = array(
                'idCurso' => $idCurso,
                'cursos' => $cursos,
                'resumen' => $resumen,
                'colorDe' => $colorDe,
                'imprimible' => $this->imprimible
            );
        else
            $datos = null;

        $titulo = array('titulo' => "Resumen por color de {$colorDe} en el curso {$cursos[$idCurso]}");

        if ($this->imprimible)
            $this->load->view('header_print', $titulo);
        else
            $this->load->view('header', $titulo);

        if (is_null($datos))
            $this->load->view('common/dberror');
        else
            $this->load->view('resumen_color', array('datos' => $datos));

        $this->load->view($this->imprimible ? 'footer_print' : 'footer');
    }

    public function printable($idCurso = null, $colorDe = 'piel') {
        $this->imprimible = true;
        $this->index($idCurso, $colorDe);
    }

}