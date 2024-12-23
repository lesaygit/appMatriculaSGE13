<?php

class Main extends CI_Controller {

    public function index() {
        $nombreAp = explode(' ', ucfirst(get_instance()->session->userdata('userFullName')));

        $this->load->view("header");
        $this->load->view('main', array('centro' => $this->session_values->datosConfig['centro'], 'primerNombre' => $nombreAp[0]));
        $this->load->view('footer');
    }

}