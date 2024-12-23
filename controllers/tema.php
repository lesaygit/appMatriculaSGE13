<?php

class Tema extends CI_Controller {

    public function index() {
        $temas = $this->common_model->temas();

        $tema = $this->input->post('tema');
        if ($tema) {
            if (!in_array($tema, $temas))
                $tema = 'default';

            $res = $this->usuario->actualizar_tema($this->session_values->idUsuario, $tema);

            if ($res === true) { //Fue satisfactorio
                $this->session->set_userdata('tema', $tema);
                redirect('tema');
                return;
            }

            $error = 'No ha sido posible completar la operación';
        } else {
            $tema = $this->session->userdata('tema');
            $error = '';
        }

        $this->load->view('header', array('titulo' => 'Inicializar curso ' . $this->curso->siguiente_str()));
        $this->load->view('tema', array('curTema' => $tema, 'temas' => $temas, 'error' => $error));
        $this->load->view('footer');
    }

}