<?php

class Change_pw extends CI_Controller {

    public function index() {
        $this->load->model('usuario');

        $this->form_validation->set_rules('oldpw', 'Contraseña actual', 'trim|required|md5');
        $this->form_validation->set_rules('newpw', 'Nueva contraseña', 'trim|required|min_length[5]|md5');
        $this->form_validation->set_rules('confirm', 'Confirmar contraseña', 'trim|required|matches[newpw]|md5');

        $success = 0;
        $error = '';

        //Chequear si la validación del formulario es satisfactoria
        if ($this->form_validation->run()) {
            //Obtener las contraseñas
            $oldPassword = $this->input->post('oldpw');
            $newPassword = $this->input->post('newpw');

            $result = $this->usuario->actualizar_password($this->session->userdata('userId'), $newPassword, $oldPassword);

            if (is_null($result))
                $error = 'No ha sido posible validar los datos.';
            elseif ($result === false)
                $error = 'No ha sido posible actualizar la contraseña.';
            else {
                $this->session->set_flashdata('success', 'La contraseña fue actualizada con éxito.');
                $this->log->Insertar('Cambió la contraseña');
                redirect('change_pw');
                return;
            }
        }

        //Mostrar el contenido
        $this->load->view('header', array('titulo' => 'Cambiar contraseña'));
        $this->load->view('change_pw', array('error' => $error));
        $this->load->view('footer');
    }

}