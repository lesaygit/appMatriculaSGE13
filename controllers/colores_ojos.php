<?php

class Colores_ojos extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('color_ojos');
    }

    public function index($state = '', $idColor = -1, $error = '') {
        $colores = $this->color_ojos->lista(true);

        $this->load->view('header', array('titulo' => 'Colores de ojos'));

        if (is_null($colores))
            $this->load->view('common/dberror');
        else {
            if ($idColor != -1 && $idColor != 'new')
                if (!isset($colores[$idColor])) {
                    $idColor = -1;
                    $state = '';
                }

            $this->load->view('su/colores_ojos', array('idColor' => $idColor, 'colores' => $colores, 'state' => $state, 'error' => $error));
        }

        $this->load->view('footer');
    }

    /*
     * Se ejecuta al editar o adicionar un registro
     */

    public function edit($idColor) {
        $this->form_validation->set_rules('nombre', 'Color', 'trim|required|max_length[15]|callback_alpha_check');

        $error = '';

        //Chequear si la validación del formulario es satisfactoria
        if ($this->form_validation->run()) {
            //Obtener los datos
            $nombre = $this->input->post('nombre');

            $insertando = strtolower($idColor) === 'new';

            if ($insertando)
                $result = $this->color_ojos->insertar($nombre);
            else
                $result = $this->color_ojos->actualizar($idColor, $nombre);

            if ($result === TRUE) {
                $this->session->set_flashdata('success', true);

                //Redireccionar para eliminar los datos del formulario
                if ($insertando) {
                    $this->log->Insertar('Insertó el color de ojos ' . $nombre);
                    redirect('colores_ojos/edit/new');
                } else {
                    $this->log->Insertar('Actualizó un color de ojos a ' . $nombre);
                    redirect('colores_ojos');
                }

                return;
            }

            if (is_null($result))
                $error = 'No ha sido posible completar la operación.';
            elseif ($result === 1)
                $error = sprintf("El color '%s' ya existe en la base de datos", $nombre);
            else {
                if ($insertando)
                    $error = 'No ha sido posible agregar el color.';
                else
                    $error = 'No ha sido posible actualizar los datos.';
            }
        }

        $this->index('edit', $idColor, $error);
    }

    /*
     * Chequea que el campo esté compuesto solo por letras (incluyendo acentos y ñ)
     */

    public function alpha_check($str) {
        if (preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/i', $str) > 0)
            return true;

        $this->form_validation->set_message('alpha_check', 'El campo %s contiene caracteres no permitidos');
        return false;
    }

    /*
     * Se ejecuta al intentar eliminar un registro
     */

    function delete($idColor) {
        $result = $this->color_ojos->eliminar($idColor);

        if ($result === TRUE) {
            $this->session->set_flashdata('success', true);
            $this->log->Insertar('Eliminó un color de ojos');
            redirect('colores_ojos');
            return;
        }

        if ($result === 1)
            $this->index('', $idColor, 'No se puede eliminar el color porque ya está siendo utilizado.');
        else
            $this->index('', $idColor, 'No ha sido posible ' . (is_null($result) ? 'completar la operación' : 'eliminar el color') . '.');
    }

}