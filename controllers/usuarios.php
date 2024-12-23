<?php

class Usuarios extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('rol');
    }

    public function index($state = '', $idUsuario = -1, $error = '') {
        $usuarios = $this->usuario->lista();
        $roles = $this->rol->lista();

        if ($idUsuario != -1)
            $rolesUsr = $this->rol->usuario($idUsuario);
        else
            $rolesUsr = array();

        $this->load->view('header', array('titulo' => 'Administrar usuarios'));
        if (is_null($roles) || is_null($rolesUsr) || is_null($usuarios))
            $this->load->view('common/dberror');
        else {
            if ($roles)
                $this->load->view('su/usuarios', array(
                    'roles' => $roles,
                    'rolesUsr' => $rolesUsr,
                    'usuarios' => $usuarios,
                    'idUsuario' => $idUsuario,
                    'state' => $state,
                    'error' => $error));
            else
                $this->load->view('common/error', array('error' => 'Debe definir al menos un rol para poder agregar usuarios.'));
        }
        $this->load->view('footer');
    }

    /*
     * Se ejecuta al editar o adicionar un registro
     */

    public function edit($idUsuario) {
        //No chequear estos campos si es el administrador
        if ($idUsuario != 1) {
            $this->form_validation->set_rules('login', 'Usuario', 'trim|required|min_length[3]|max_length[16]|alpha');
            $this->form_validation->set_rules('roles', 'Roles', 'callback_roles_check');
        }

        $this->form_validation->set_rules('nombre', 'Nombre', 'trim|required|min_length[3]|max_length[25]|callback_alpha_check');
        $this->form_validation->set_rules('apellidos', 'Apellidos', 'trim|required|min_length[3]|max_length[30]|callback_alpha_check');
        
        $insertando = strtolower($idUsuario) === 'new';

        if ($insertando) {
            $this->form_validation->set_rules('passw', 'Contraseña', 'trim|required|min_length[4]|md5');
            $this->form_validation->set_rules('pwconf', 'Confirmar contraseña', 'trim|required|matches[passw]|md5');
        }

        $error = '';

        //Chequear si la validación del formulario es satisfactoria
        if ($this->form_validation->run()) {
            //Obtener los datos
            $login = $idUsuario == 1 ? '' : $this->input->post('login');
            $nombre = $this->input->post('nombre');
            $apellidos = $this->input->post('apellidos');
            $pw = $this->input->post('passw');
            $roles = $this->input->post('roles');

            if ($insertando)
                $result = $this->usuario->insertar($login, $nombre, $apellidos, $pw, $roles);
            else
                $result = $this->usuario->actualizar($idUsuario, $login, $nombre, $apellidos, $roles);

            if ($result === TRUE) {
                //Redireccionar para eliminar los datos del formulario
                if ($insertando) {
                    $this->session->set_flashdata('success', 'El usuario ha sido agregado con éxito.');
                    $this->log->Insertar('Agregó el usuario ' . $nombre . ' ' . $apellidos);
                    redirect('usuarios/edit/new');
                } else {
                    $this->session->set_flashdata('success', 'El usuario ha sido actualizado con éxito.');
                    $this->log->Insertar('Actualizó el usuario ' . $nombre . ' ' . $apellidos);
                    redirect('usuarios/');
                }

                return;
            }

            if (is_null($result))
                $error = 'No ha sido posible completar la operación.';
            elseif ($result === 1)
                $error = sprintf("La cuenta '%s' ya existe en la base de datos", $login);
            else {
                if ($insertando)
                    $error = 'No ha sido posible agregar el usuario.';
                else
                    $error = 'No ha sido posible actualizar los datos.';
            }
        }

        $this->index('edit', $idUsuario, $error);
    }

    /*
     * Se ejecuta al cambiar la contraseña de un usuario
     */

    public function chpw($idUsuario) {
        $this->form_validation->set_rules('passw', 'Contraseña', 'trim|required|min_length[5]|md5');
        $this->form_validation->set_rules('pwconf', 'Confirmar contraseña', 'trim|required|matches[passw]|md5');

        $error = '';

        //Chequear si la validación del formulario es satisfactoria
        if ($this->form_validation->run()) {
            //Obtener los datos
            $result = $this->usuario->actualizar_password($idUsuario, $this->input->post('passw'));

            if ($result === TRUE) {
                $this->session->set_flashdata('success', 'La contraseña del usuario ha sido actualizada con éxito.');
                //Redireccionar para eliminar los datos del formulario
                redirect('usuarios/');

                return;
            }

            $error = 'No ha sido posible actualizar la contraseña.';
        }

        $this->index('chpw', $idUsuario, $error);
    }

    /*
     * Se ejecuta al intentar eliminar un registro
     */

    function delete($idUsuario) {
        $result = $this->usuario->eliminar($idUsuario);

        if ($result === TRUE) {
            $this->session->set_flashdata('success', 'El usuario ha sido eliminado con éxito.');
            $this->log->Insertar('Eliminó el usuario con Id=' . $idUsuario);
            redirect('usuarios/');
        } else
        if ($result == -1)
            $this->index('', $idUsuario, 'No se puede eliminar el usuario administrador.');
        else
            $this->index('', $idUsuario, 'No ha sido posible ' . (is_null($result) ? 'completar la operación' : 'eliminar el usuario') . '.');
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

    public function roles_check($roles) {
        if (is_array($roles) && $roles)
            return true;

        $this->form_validation->set_message('roles_check', 'Debe especificar al menos un rol al usuario.');
        return false;
    }

}