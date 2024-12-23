<?php

class Make_config extends CI_Controller {

    public function index() {
        //Si la configuración existe no hay que hacer nada
        if ($this->common_model->config_exists()) {
            if ($this->session->userdata('logged'))
                redirect('main');
            else
                redirect('login');

            return;
        }

        $this->session->unset_userdata('logged');
        $this->session->unset_userdata('tema');

        $year = date('Y');

        $this->form_validation->set_rules('centro', 'Nombre del centro', 'trim|required|min_length[5]|max_length[80]|xss_clean');
        $this->form_validation->set_rules('direccion', 'Dirección del centro', 'trim|required|min_length[5]|max_length[80]|callback_alpha_sym_check|xss_clean');
        $this->form_validation->set_rules('localidad', 'Nombre de la localidad', 'trim|required|min_length[3]|max_length[40]|xss_clean|callback_alpha_check');
        $this->form_validation->set_rules('adm_name', 'Nombre del administrador', 'trim|required|min_length[3]|max_length[25]|xss_clean|callback_alpha_check');
        $this->form_validation->set_rules('adm_apel', 'Apellidos del administrador', 'trim|required|min_length[5]|max_length[30]|xss_clean|callback_alpha_check');
        $this->form_validation->set_rules('password', 'Contraseña', 'trim|required|matches[passconf]|md5');
        $this->form_validation->set_rules('passconf', 'Confirmar contraseña', 'trim|required');
        $this->form_validation->set_rules('a_inicial', 'Año inicial', 'trim|required|integer|greater_than[' . ($year - 1) . ']|less_than[' . ($year + 1) . ']');

        if ($this->form_validation->run()) {
            $result = $this->common_model->create_config(set_value('a_inicial'), $_POST['idMunicipio'], set_value('localidad'), set_value('adm_name'), set_value('adm_apel'), set_value('password'), set_value('centro'), set_value('direccion'));

            if ($result === TRUE) {
                //redirect('login');
                $this->session->set_userdata('isAdmin', true);
                $this->session->set_userdata('logged', true);
                $this->session->set_userdata('userId', 1);
                $this->session->set_userdata('tema', 'default');

                redirect('main');
                return;
            }

            if ($result === 1)
                $error = 'Ya existen datos en la base de datos';
            elseif (is_null($result))
                $error = 'No ha sido posible verificar la integridad de la base de datos';
            else
                $error = 'No ha sido posible guardar la configuración';
        }
        else
            $error = null;

        $provs = $this->common_model->get_provincias();
        $muns = $this->common_model->get_municipios_prov();

        $this->load->view('login/header');

        if (is_null($provs) || is_null($muns))
            $this->load->view('common/dberror');
        elseif ($error)
            $this->load->view('common/error', array('error' => $error));
        else {
            $this->load->view('su/make_config');
            $this->load->view('js/actualizarMunicipios', array('provincias' => $provs, 'municipios' => $muns));
        }

        $this->load->view('login/footer');
    }

    /*
     * Chequea que el campo esté compuesto solo por letras y espacios (incluyendo acentos y ñ)
     */

    public function alpha_check($str) {
        if (preg_match('/^[\s\.a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/i', $str) > 0)
            return true;

        $this->form_validation->set_message('alpha_check', 'El campo %s contiene caracteres no permitidos');
        return false;
    }

    /*
     * Callback. Chequea que el campo esté compuesto solo por letras, números y espacios (incluyendo acentos y ñ) y algunos símbolos
     */

    public function alpha_sym_check($str) {
        if ($str == '' || preg_match('/^[\s\.0-9a-zA-ZáéíóúÁÉÍÓÚñÑ#%,"\'\/\-]+$/i', $str) > 0)
            return true;

        $this->form_validation->set_message('alpha_sym_check', 'El campo %s contiene caracteres no permitidos');
        return false;
    }
}