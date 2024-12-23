<?php

class Procesar extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('grupo');
        $this->load->model('estudiante');
        $this->load->model('estados');
    }

    public function index($idGrado = null, $idGrupo = null, $error = '') {
        //Es necesario que estén conformados los grupos
        $grupos = $this->grupo->lista($this->curso->activo_id());
        $grupos_sig = $this->grupo->lista($this->curso->siguiente_id());
        $estados = $this->estados->lista();

        if (empty($grupos) || empty($grupos_sig) || empty($estados)) {
            $this->load->view('header', array('titulo' => 'Procesar estudiantes'));
            if (is_null($grupos) || is_null($grupos_sig) || is_null($estados))
                $this->load->view('common/dberror');
            else {
                if (!$this->curso->siguiente_id())
                    $error = 'El curso siguiente no ha sido inicializado.';
                elseif (is_array($grupos_sig))
                    $error = 'No se han definido grupos para el curso siguiente';
                else
                    $error = 'No se ha obtenido suficientes datos para mostrar el formulario.';

                $this->load->view('common/error', array('error' => $error));
            }
            $this->load->view('footer');
            $this->output->_display();
            die();
        }

        //Chequear que el grado sea válido
        if (!in_array($idGrado, array(7, 8, 9)))
            $idGrado = 7;

        //Comprobar si existe el grupo en el grado especificado
        if (!isset($grupos[$idGrupo]) || $grupos[$idGrupo]['grado'] != $idGrado) {
            //Obtener el grado y el grupo que deben aparecer seleccionados en las listas
            $v = $this->input->post('idGrado');
            if ($v) {
                $idGrado = $v;
                $idGrupo = $this->input->post('idGrupo');
            } else {
                $idGrupo = key($grupos);
                $idGrado = $grupos[$idGrupo]['grado'];
            }
        }

        //Obtener los estudiantes del curso activo y los del curso siguiente
        $estudiantes = $this->estudiante->lista($idGrupo);
        if (is_array($estudiantes)) {
            $gruposSig = $this->estudiante->grupos_curso_siguiente();
            if (is_array($gruposSig)) {
                $grupoEstud = $this->input->post('grupo');
                if (!$grupoEstud)
                    $grupoEstud = $gruposSig;

                $datos = array(
                    'estudiantes' => $estudiantes,
                    'estados' => $estados,
                    'grupos' => $grupos,
                    'grupos_sig' => $grupos_sig,
                    'idGrupo' => $idGrupo,
                    'idGrado' => $idGrado,
                    'estadoEstud' => $this->input->post('estado'),
                    'grupoEstud' => $grupoEstud
                );
            }
            else
                $datos = null;
        }
        else
            $datos = null;

        $this->load->view('header', array('titulo' => 'Procesar estudiantes'));

        if (is_null($datos))
            $this->load->view('common/dberror');
        else {
            $datos['error'] = $error;

            $this->load->view('procesar', $datos);
            $this->load->view('js/actualizarGrupos', array('grupos' => $grupos, 'idGrado' => $idGrado, 'idGrupo' => $idGrupo));
        }

        $this->load->view('footer');
    }

    /*
     * Se ejecuta al guardar los cambios
     */

    public function guardar($idGrado = null, $idGrupo = null) {
        $error = '';
        $estado = $this->input->post('estado');

        if (is_array($estado) && $estado) {
            $grupo = $this->input->post('grupo');
            if (!$grupo)
                $grupo = array();

            $res = $this->estudiante->procesar_lote($estado, $grupo);

            if ($res === TRUE) {
                $this->session->set_flashdata('success', 'Los datos fueron guardados satisfactoriamente.');
                $this->log->Insertar('Procesó los estudiantes del grupo ' . $idGrupo . ' del curso ' . $this->curso->activo_str());
                redirect('procesar/index/' . $idGrado . '/' . $idGrupo);
                return;
            }

            if (is_null($res))
                $error = 'No ha sido posible completar la operación.';
            elseif ($res === 0)
                $error = 'Los datos no son congruentes en su totalidad.';
            else
                $error = 'No ha sido posible actualizar los datos.';
        }

        $this->index($idGrado, $idGrupo, $error);
    }

}