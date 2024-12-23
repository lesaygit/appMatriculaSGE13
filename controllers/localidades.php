<?php

class Localidades extends CI_Controller {

    public function __construct() {
        parent::__construct();

        //Cargar el modelo de localidades
        $this->load->model('localidad');
    }

    public function index($state = '', $idLocalidad = -1, $error = '') {
        $localidades = $this->localidad->lista(null, true);

        $this->load->view('header', array('titulo' => 'Administrar localidades'));
        if (is_null($localidades))
            $this->load->view('common/dberror');
        else
            $this->load->view('su/localidades', array('localidades' => $localidades, 'idLocalidad' => $idLocalidad, 'state' => $state, 'error' => $error));
        $this->load->view('footer');
    }

    /*
     * Se ejecuta al editar o adicionar un registro
     */

    public function edit($idLocalidad) {
        $this->form_validation->set_rules('nombre', 'Nombre', 'trim|required|min_length[3]|max_length[40]|callback_alpha_check');

        $error = '';

        //Chequear si la validación del formulario es satisfactoria
        if ($this->form_validation->run()) {
            //Obtener los datos
            $nombre = $this->input->post('nombre');

            $insertando = strtolower($idLocalidad) === 'new';

            if ($insertando)
                $result = $this->localidad->insertar($nombre);
            else
                $result = $this->localidad->actualizar($idLocalidad, $nombre);

            if ($result === TRUE) {
                //Redireccionar para eliminar los datos del formulario
                if ($insertando) {
                    $this->session->set_flashdata('success', 'La localidad fue agregada con éxito');
                    $this->log->Insertar('Agregó la localidad ' . $nombre);
                    redirect('localidades/edit/new');
                } else {
                    $this->session->set_flashdata('success', 'La localidad fue actualizada con éxito');
                    $this->log->Insertar('Actualizó la localidad ' . $nombre);
                    redirect('localidades/');
                }

                return;
            }

            if (is_null($result))
                $error = 'No ha sido posible completar la operación.';
            elseif ($result === 1)
                $error = sprintf("La localidad '%s' ya existe en la base de datos", $nombre);
            elseif ($result === 0)
                $error = 'Se ha detectado un problema en la configuración.';
            else {
                if ($insertando)
                    $error = 'No ha sido posible agregar la localidad.';
                else
                    $error = 'No ha sido posible actualizar los datos de la localidad.';
            }
        }

        $this->index('edit', $idLocalidad, $error);
    }

    /*
     * Callback. Chequea que el campo esté compuesto solo por letras (incluyendo acentos y ñ), números, puntos y espacios
     */

    public function alpha_check($str) {
        if (preg_match('/^[\s\.0-1a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/i', $str) > 0)
            return true;

        $this->form_validation->set_message('alpha_check', 'El campo %s contiene caracteres no permitidos');
        return false;
    }

    /*
     * Se ejecuta al intentar eliminar un registro
     */

    function delete($idLocalidad) {
        $result = $this->localidad->eliminar($idLocalidad);

        if ($result) {
            $this->session->set_flashdata('success', 'La localidad fue eliminada con éxito');
            $this->log->Insertar('Eliminó la localidad con Id=' . $idLocalidad);
            redirect('localidades/');
        }
        else
            $this->index('', $idLocalidad, 'No ha sido posible ' . (is_null($result) ? 'completar la operación' : 'eliminar la localidad') . ' .');
    }

}