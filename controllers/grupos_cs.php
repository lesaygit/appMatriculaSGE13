<?php

class Grupos_cs extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('grupo');
    }

    public function index($state = '', $grado = 7, $idGrupo = -1, $error = '') {
        $grupos = $this->grupo->lista($this->curso->siguiente_id());

        $titulo = 'Conformar grupos del curso ' . $this->curso->siguiente_str();

        $this->load->view('header', array('titulo' => $titulo));

        if (is_null($grupos))
            $this->load->view('common/dberror');
        else
            $this->load->view('grupos_cs', array('grado' => $grado, 'idGrupo' => $idGrupo, 'grupos' => $grupos, 'state' => $state, 'error' => $error));

        $this->load->view('footer');
    }

    /*
     * Se ejecuta al editar o adicionar un registro
     */

    public function edit($idGrupo, $grado = 7) {
        $this->form_validation->set_rules('nombre', 'Número/Letra', 'trim|required|max_length[2]|alpha_numeric');

        $error = '';

        //Chequear si la validación del formulario es satisfactoria
        if ($this->form_validation->run()) {
            //Obtener los datos
            $grado = $this->input->post('grado');
            $nombre = $this->input->post('nombre');

            $insertando = strtolower($idGrupo) === 'new';

            if ($insertando)
                $result = $this->grupo->insertar($grado, $nombre, false);
            else
                $result = $this->grupo->actualizar($idGrupo, $grado, $nombre, false);

            if ($result === TRUE) {
                //Redireccionar para eliminar los datos del formulario
                if ($insertando) {
                    $this->session->set_flashdata('success', 'El grupo fue agregado con éxito');
                    $this->log->Insertar('Agregó el grupo ' . $grado . '-' . $nombre . ' del curso ' . $this->curso->siguiente_str());
                    redirect('grupos_cs/edit/new/' . $grado);
                } else {
                    $this->session->set_flashdata('success', 'El grupo fue actualizado con éxito');
                    $this->log->Insertar('Actualizó el grupo ' . $grado . '-' . $nombre . ' del curso ' . $this->curso->siguiente_str());
                    redirect('grupos_cs/');
                }

                return;
            }

            if (is_null($result))
                $error = 'No ha sido posible completar la operación.';
            elseif ($result === 1)
                $error = sprintf("El grupo '%s' ya existe en la base de datos", $grado . '-' . strtoupper($nombre));
            else {
                if ($insertando)
                    $error = 'No ha sido posible agregar el grupo.';
                else
                    $error = 'No ha sido posible actualizar los datos.';
            }
        }

        $this->index('edit', $grado, $idGrupo, $error);
    }

    /*
     * Se ejecuta al intentar eliminar un registro
     */

    function delete($idGrupo) {
        $result = $this->grupo->eliminar($idGrupo);

        if ($result === TRUE) {
            $this->session->set_flashdata('success', 'El grupo fue eliminado con éxito');
            $this->log->Insertar('Eliminó el grupo con Id=' . $idGrupo . ' del curso ' . $this->curso->siguiente_str());
            redirect('grupos_cs/');
            return;
        }

        if ($result === 1)
            $this->index('', $idGrupo, 'No se puede eliminar el grupo porque tiene estudiantes.');
        else
            $this->index('', $idGrupo, 'No ha sido posible ' . (is_null($result) ? 'completar la operación' : 'eliminar el grupo') . '.');
    }

}