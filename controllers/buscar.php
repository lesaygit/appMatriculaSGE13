<?php

class Buscar extends CI_Controller {

    public function index() {
        $this->load->model('estudiante');
        $this->load->library('pagination');

        $this->form_validation->set_rules('field', 'Buscar en', 'trim|integer|greater_than[0]|less_than[7]');
        $this->form_validation->set_rules('curso', 'Curso', 'trim|integer|greater_than[0]|less_than[3]');
        $this->form_validation->set_rules('texto', 'Texto a buscar', 'trim|required|callback_alpha_sym_check');

        $likeType = $this->input->post('likeType');
        if (!is_numeric($likeType) || $likeType < 0 && $likeType > 3)
            $likeType = $_POST['likeType'] = 1;

        if ($this->form_validation->run()) {
            $texto = $this->input->post('texto');
            $field = $this->input->post('field');
            $curso = $this->input->post('curso');

            //Obtener los estudiantes que cumplen la condición de búsqueda
            $estudiantes = $this->estudiante->buscar($field, $texto, $curso == '1', $likeType);
        }
        else
            $estudiantes = array();

        if (is_array($estudiantes)) {
            $page = $this->input->post('page');
            if (!is_numeric($page))
                $page = 1;

            $perPage = 20;
            $pagerConfig = $this->common_model->get_paginator_config(count($estudiantes), 'buscar/', 3, $perPage, $page, $offset);
            
            $this->pagination->initialize($pagerConfig);

            $datos = array(
                'estudiantes' => $estudiantes,
                'likeType' => $likeType,
                'findFields' => array(
                    1 => 'C. de identidad',
                    2 => 'Nombre',
                    3 => 'Primer apellido',
                    4 => 'Segundo apellido',
                    5 => 'Apellidos',
                    6 => 'Nombre completo'
                ),
                'page' => $page,
                'offset' => $offset,
                'perPage' => $perPage
            );
        } else
            $datos = null;

        $this->load->view('header', array('titulo' => 'Buscar estudiantes'));

        if (is_null($datos))
            $this->load->view('common/dberror');
        else
            $this->load->view('buscar', array('datos' => $datos));

        $this->load->view('footer');
    }

    /*
     * Chequea que el campo esté compuesto solo por letras y espacios (incluyendo acentos y ñ) y algunos símbolos
     */

    public function alpha_sym_check($str) {
        if (preg_match('/^[\s\.0-9a-zA-ZáéíóúÁÉÍÓÚñÑ#%,"\'\/\-]+$/i', $str) > 0)
            return true;

        $this->form_validation->set_message('alpha_sym_check', 'El campo %s contiene caracteres no permitidos');
        return false;
    }

}