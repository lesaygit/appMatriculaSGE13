<?php

class Access_checker extends CI_Controller {

    public function index() {
        //Poner la zona horaria de Cuba
        date_default_timezone_set('Chile/EasterIsland');

        //Si se llama la página sin incluir index.php trae consigo que se rompan algunos vínculos.
        //Con esto se logra insertar index.php en el URL del navegador
        if (strpos($_SERVER['REQUEST_URI'], 'index.php') === FALSE) {
            redirect('main');
            return;
        }

        $controller = $this->uri->segments ? strtolower($this->uri->segments[1]) : 'main';

        //Chequear si existe la configuración
        $cfg = $this->common_model->config_exists();
        if (!$cfg) {
            if ($cfg === FALSE) {
                if ($controller != 'make_config')
                    redirect('make_config');

                return;
            }

            //No se pudo determinar la existencia de la configuración. Mostrar error
            $this->load->view('login/header');
            $this->load->view('common/dberror');  //Error con la BD
            $this->load->view('login/footer');
            $this->output->_display();            //Enviar al navegador
            die;                                  //Terminar todo                
        }

        //Obtener y guardar en la sesión el curso activo
        $cursoActivo = $this->curso->activo();
        $cursoSiguiente = $this->curso->siguiente();
        $configData = $this->common_model->config_datos();

        if (!is_array($cursoActivo) || is_null($cursoSiguiente) || is_null($configData)) {
            $this->load->view('login/header');
            $this->load->view('common/dberror');
            $this->load->view('login/footer');
            $this->output->_display();
            die;
        }

        //Si llega a este punto $cursoActivo es array y $cursoSiguiente es array o FALSE
        $this->session_values->cursoActivo = $cursoActivo;
        $this->session_values->cursoSiguiente = $cursoSiguiente;
        $this->session_values->datosConfig = $configData;
        $this->session_values->idUsuario = $this->session->userdata('userId');

        //Verificar si está autentificado
        if ($this->session->userdata('logged')) {
            if ($controller == 'login') {
                //No es necesario entrar a login, redireccionar a la página main
                redirect('main');
                return;
            }

            if ($this->session->userdata('isAdmin'))
                $permisos = array();
            else {
                $permisos = $this->usuario->permisos($this->session->userdata('userId'));
                if (is_null($permisos)) {
                    $this->load->view('login/header');
                    $this->load->view('common/dberror');
                    $this->load->view('login/footer');
                    $this->output->_display();
                    die;
                }
            }

            $this->session_values->permisos = $permisos;

            if ($this->permisos->puede_ejecutar($controller))
                return;

            //Si llega aquí el usuario no puede ejecutar la página. Mostrar una página con el error y terminar
            $this->load->view('header');
            $this->load->view('common/no_autorizado');  //No tiene permisos
            $this->load->view('footer');
            $this->output->_display();                  //Enviar al navegador
            die;                                        //Terminar todo
        }

        //No se ha autentificado. Redireccionar a la página login, excepto si es la página de login
        if ($controller != 'login')
            redirect('login');
    }

}