<?php

class Login extends CI_Controller {

    public function index() {
        $this->form_validation->set_rules('username', 'Usuario', 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Contraseña', 'trim|required|md5');

        $error = '';

        //Chequear si la validación del formulario es satisfactoria
        if ($this->form_validation->run()) {
            //Obtener el nombre de usuario y el password
            $user_name = $this->input->post('username');
            $password = $this->input->post('password');

            $result = $this->usuario->validar_password($user_name, $password);

            if (is_null($result))
                $error = 'No ha sido posible validar los datos.';
            elseif ($result === false)
                $error = "Usuario y contraseña incorrectos.";
            else {
                //Obtener si es admninistrador
                $admin = $this->usuario->es_administrador($result->idusuario);

                if (is_null($admin))
                    $error = 'No ha sido posible validar los datos.';
                else {
                    //Guardar en la sesión los datos del usuario
                    $this->session->set_userdata('isAdmin', $admin);
                    $this->session->set_userdata('logged', true);
                    $this->session->set_userdata('userId', $result->idusuario);
                    $this->session->set_userdata('tema', $result->tema);
                    
                    $this->session_values->idUsuario = $result->idusuario;
                    
                    $this->log->Insertar($result->nombre . ' ' . $result->apellidos . ' se autentificó correctamente');

                    //Se autentificó correctamente, redireccionar a la página principal
                    redirect('main');
                    return;
                }
            }
        } elseif (isset($_POST['username']))
            $error = "Usuario y contraseña incorrectos.";

        //Mostrar el contenido
        $this->load->view('login/header');
        $this->load->view('login/login', array('error' => $error));
        $this->load->view('login/footer');
    }

}