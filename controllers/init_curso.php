<?php

class Init_curso extends CI_Controller {

    public function index() {
        $error = '';

        if ($this->curso->siguiente_id())
            $error = -1; //El curso siguiente ya está inicializado, en la vista mostrar un texto explicativo
        else {
            if ($this->input->post('proceed')) {
                $res = $this->curso->insertar_siguiente();
                
                if ($res === true) { //Fue satisfactorio
                    $this->session->set_flashdata('success', 'El curso ' . $this->curso->siguiente_str() . ' ha sido inicializado con éxito.');
                    $this->log->Insertar('Inicializó el curso ' . $this->curso->siguiente_str());
                    redirect('init_curso');
                    return;
                }

                if ($res == -1)
                    $error = -1;
                elseif (is_null($res) || $res === false)
                    $error = 'No ha sido posible completar la operación';
            }
        }

        $this->load->view('header', array('titulo' => 'Inicializar curso ' . $this->curso->siguiente_str()));
        $this->load->view('init_curso', array('error' => $error));
        $this->load->view('footer');
    }

}