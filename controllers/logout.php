<?php

class Logout extends CI_Controller {

    public function index() {
        $this->session->unset_userdata('logged');
        $this->session->unset_userdata('isAdmin');
        $this->session->unset_userdata('userId');
        $this->session->unset_userdata('tema');

        $this->log->Insertar('Cerró la sesión');
        
        redirect('login');
    }

}