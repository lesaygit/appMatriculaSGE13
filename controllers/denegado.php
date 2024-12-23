<?php

class Denegado extends CI_Controller {

    public function index() {
        $this->load->view('header', array('titulo' => ''));
        $this->load->view('common/no_autorizado');
        $this->load->view('footer');
    }

}