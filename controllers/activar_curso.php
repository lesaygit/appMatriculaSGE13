<?php

class Activar_curso extends CI_Controller {

    public function index() {
        $error = '';
        
        $idCursoSig = $this->curso->siguiente_id();

        if (!($idCursoSig && $this->curso->siguiente_es_activable())) {
            $error = -1;
            $titulo = 'Activar curso siguiente';
        } else {
            $titulo = 'Activar curso ' . $this->curso->siguiente_str();

            if ($this->input->post('proceed')) {
                $res = $this->curso->activar_siguiente();

                if ($res === -1)
                    $error = -1;
                elseif (is_null($res) || $res === false)
                    $error = 'No ha sido posible completar la operación';
                else {
                    $this->log->Insertar('Activó el curso: ' . $this->curso->siguiente_str());
                    
                    //Poner en la sesión la variable success hasta la próxima recarga de la página
                    $this->session->set_flashdata('success', 'El curso ' . $this->curso->activo_str() . ' ha sido activado satisfactoriamente.');
                    redirect('main');
                    return;
                }
            }
        }

        $this->load->view('header', array('titulo' => $titulo));
        $this->load->view('activar_curso', array('error' => $error));
        $this->load->view('footer');
    }

}