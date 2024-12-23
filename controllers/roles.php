<?php

class Roles extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('rol');
        $this->load->model('permisos');
    }

    public function index($state = '', $idRol = -1, $error = '') {
        $roles = $this->rol->lista();
        $permisos = $this->permisos->lista();
        if ($idRol != -1)
            $perm_rol = $this->rol->permisos($idRol);
        else
            $perm_rol = array();

        $this->load->view('header', array('titulo' => 'Administrar roles'));
        if (is_null($roles) || is_null($permisos) || is_null($perm_rol))
            $this->load->view('common/dberror');
        else
            $this->load->view('su/roles', array(
                'roles' => $roles,
                'permisos' => $permisos,
                'perm_rol' => $perm_rol,
                'idRol' => $idRol,
                'state' => $state,
                'error' => $error));
        $this->load->view('footer');
    }

    /*
     * Se ejecuta al editar o adicionar un registro
     */

    public function edit($idRol) {
        $this->form_validation->set_rules('nombre', 'Nombre', 'trim|required|min_length[3]|max_length[45]|callback_alpha_check');

        $error = '';

        //Chequear si la validación del formulario es satisfactoria
        if ($this->form_validation->run()) {
            //Obtener los datos
            $nombre = $this->input->post('nombre');
            $permisos = $this->input->post('permisos');

            $insertando = strtolower($idRol) === 'new';

            if ($insertando)
                $result = $this->rol->insertar($nombre, $permisos);
            else
                $result = $this->rol->actualizar($idRol, $nombre, $permisos);

            if ($result === TRUE) {
                //Redireccionar para eliminar los datos del formulario
                if ($insertando) {
                    $this->session->set_flashdata('success', true);
                    $this->log->Insertar('Agregó rol ' . $nombre);
                    redirect('roles/edit/new');
                } else {
                    $this->session->set_flashdata('success', true);
                    $this->log->Insertar('Actualizó rol ' . $nombre);
                    redirect('roles/');
                }

                return;
            }

            if (is_null($result))
                $error = 'No ha sido posible completar la operación.';
            elseif ($result === 1)
                $error = sprintf("El rol '%s' ya existe en la base de datos", $nombre);
            else {
                if ($insertando)
                    $error = 'No ha sido posible agregar el rol.';
                else
                    $error = 'No ha sido posible actualizar los datos.';
            }
        }

        $this->index('edit', $idRol, $error);
    }

    /*
     * Se ejecuta al intentar eliminar un registro
     */

    function delete($idRol) {
        $result = $this->rol->eliminar($idRol);

        if ($result === TRUE) {
            $this->session->set_flashdata('success', true);
            $this->log->Insertar('Eliminó el rol ' . $idRol);
            redirect('roles/');
        } else
            $this->index('', $idRol, 'No ha sido posible ' . (is_null($result) ? 'completar la operación' : 'eliminar el rol') . '.');
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

}