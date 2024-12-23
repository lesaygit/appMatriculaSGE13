<?php

class Detalle extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('estudiante');
    }

    public function index($idEstudiante = -1) {
        //Obtener los datos del estudiantes
        $datosEstud = $this->estudiante->datos($idEstudiante);

        $this->load->view('header_print');

        if (is_null($datosEstud))
            $this->load->view('common/dberror');
        else
            $this->load->view('detalle', array('datos' => $datosEstud));

        $this->load->view('footer_print');
    }

}