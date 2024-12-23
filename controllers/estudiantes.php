<?php

class Estudiantes extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('grupo');
        $this->load->model('estudiante');
        $this->load->model('color_ojos');
        $this->load->model('color_piel');
        $this->load->model('color_pelo');
        $this->load->model('localidad');
        $this->load->model('usuario');
    }

    public function index($idGrado = null, $idGrupo = null, $state = '', $idEstudiante = -1, $error = '') {
        $this->show($idGrado, $idGrupo, $state, $idEstudiante, $error);
    }

    public function show($idGrado = null, $idGrupo = null, $state = '', $idEstudiante = -1, $error = '') {
        //Es necesario que estén conformados los grupos
        $grupos = $this->grupo->lista($this->curso->activo_id());
        if (empty($grupos)) {
            $this->load->view('header', array('titulo' => 'Gestionar estudiantes del curso ' . $this->curso->activo_str()));
            if (is_null($grupos))
                $this->load->view('common/dberror');
            else
                $this->load->view('common/error', array('error' => 'No se ha definido ningún grupo todavía'));
            $this->load->view('footer');
            $this->output->_display();
            die();
        }

        $datos = array('grupos' => $grupos);

        if ($state) {
            $cOjos = $this->color_ojos->lista();
            $cPiel = $this->color_piel->lista();
            $cPelo = $this->color_pelo->lista();
            $localidades = $this->localidad->lista(array('0' => '-Elegir localidad-'));

            foreach (explode(',', 'cOjos,cPiel,cPelo,localidades') as $dato) {
                if (empty($$dato)) {
                    $this->load->view('header', array('titulo' => 'Gestionar estudiantes del curso ' . $this->curso->activo_str()));
                    $this->load->view('common/dberror');
                    $this->load->view('footer');
                    $this->output->_display();
                    die();
                }
                $datos[$dato] = $$dato;
            }
        }

        //Comprobar si existe el grupo en el grado especificado
        if (!isset($grupos[$idGrupo]) || $grupos[$idGrupo]['grado'] != $idGrado) {
            //Obtener el grado y el grupo que deben aparecer seleccionados en las listas
            $v = $this->input->post('idGrado');
            if ($v) {
                $idGrado = $v;
                $idGrupo = $this->input->post('idGrupo');
            } else {
                $idGrupo = key($grupos);
                $idGrado = $grupos[key($grupos)]['grado'];
            }
        }

        $datos['idGrupo'] = $idGrupo;
        $datos['idGrado'] = $idGrado;

        $estudiantes = $this->estudiante->lista($idGrupo);
        if (is_array($estudiantes))
            $datos['estudiantes'] = $estudiantes;
        else
            $datos = null;

        $this->load->view('header', array('titulo' => 'Gestionar estudiantes del curso ' . $this->curso->activo_str()));

        if (is_null($datos))
            $this->load->view('common/dberror');
        else {
            $this->load->view('js/actualizarGrupos', array('grupos' => $grupos, 'idGrado' => $idGrado, 'idGrupo' => $idGrupo));
            $this->load->view('estudiantes', array('idEstudiante' => $idEstudiante, 'datos' => $datos, 'state' => $state, 'error' => $error));
        }

        $this->load->view('footer');
    }

    /*
     * Se ejecuta al editar o adicionar un registro
     */

    public function edit($idEstudiante, $idGrado = null, $idGrupo = null) {
        $error = '';

        if ($this->input->post('sexo')) {
            $this->form_validation->set_rules('nombre', 'Nombre', 'trim|required|min_length[3]|max_length[25]|callback_alpha_check');
            $this->form_validation->set_rules('apellido1', 'Primer apellido', 'trim|required|min_length[2]|max_length[15]|callback_alpha_check');
            $this->form_validation->set_rules('apellido2', 'Segundo apellido', 'trim|required|min_length[2]|max_length[15]|callback_alpha_check');
            $this->form_validation->set_rules('CI', 'Carnet de identidad', 'trim|required|is_natural_no_zero|exact_length[11]');
            $this->form_validation->set_rules('direccion', 'Dirección', 'trim|min_length[9]|callback_alpha_sym_check');
            $this->form_validation->set_rules('localidad', 'Localidad', 'trim|required|callback_localidad_chk');
            $this->form_validation->set_rules('talla', 'Talla', 'trim|integer|greater_than[100]|less_than[200]');
            $this->form_validation->set_rules('peso', 'Peso', 'trim|integer|greater_than[25]|less_than[100]');
            $this->form_validation->set_rules('localidad', 'Localidad', 'trim|integer|callback_localidad_chk');

            //Chequear si la validación del formulario es satisfactoria
            if ($this->form_validation->run()) {
                //Obtener los datos
                $datos = array();
                $datos['nombre'] = $this->input->post('nombre');
                $datos['apellido1'] = $this->input->post('apellido1');
                $datos['apellido2'] = $this->input->post('apellido2');
                $datos['CI'] = $this->input->post('CI');
                $datos['sexo'] = $this->input->post('sexo') == 'F' ? 'F' : 'M';
                $datos['localidad'] = (int) $this->input->post('localidad');
                $datos['direccion'] = $this->input->post('direccion') ? $this->input->post('direccion') : null;
                $datos['talla'] = $this->input->post('talla') ? $this->input->post('talla') : null;
                $datos['peso'] = $this->input->post('peso') ? $this->input->post('peso') : null;
                $datos['color_ojos'] = (int) $this->input->post('color_ojos') ? $this->input->post('color_ojos') : null;
                $datos['color_pelo'] = (int) $this->input->post('color_pelo') ? $this->input->post('color_pelo') : null;
                $datos['color_piel'] = (int) $this->input->post('color_piel') ? $this->input->post('color_piel') : null;

                $insertando = strtolower($idEstudiante) === 'new';

                if ($insertando)
                    $result = $this->estudiante->matricular($this->input->post('idGrupo'), $datos);
                else
                    $result = $this->estudiante->actualizar($this->input->post('idEstudiante'), $datos);

                if ($result === TRUE) {
                    $this->session->set_flashdata('success', $insertando ? 'El estudiante ha sido agregado con éxito.' : 'Los datos del estudiante han sido actualizados con éxito.');

                    //Redireccionar para eliminar los datos del formulario
                    if ($insertando) {
                        $this->log->Insertar('Agregó el estudiante: ' . $datos['nombre'] . ' ' . $datos['apellido1'] . ' ' . $datos['apellido2']);
                        redirect('estudiantes/edit/new/' . $idGrado . '/' . $idGrupo);
                    } else {
                        $this->log->Insertar('Actualizó los datos del estudiante: ' . $datos['nombre'] . ' ' . $datos['apellido1'] . ' ' . $datos['apellido2']);
                        redirect('estudiantes/show/' . $idGrado . '/' . $idGrupo);
                    }

                    return;
                }

                if (is_null($result))
                    $error = 'No ha sido posible completar la operación.';
                elseif ($result === 1)
                    $error = sprintf("Ya existe un estudiante matriculado con el carnet de identidad '%s'", $datos['CI']);
                else {
                    if ($insertando)
                        $error = 'No ha sido posible matricular el estudiante.';
                    else
                        $error = 'No ha sido posible actualizar los datos.';
                }
            }
        }

        $this->index($idGrado, $idGrupo, 'edit', $idEstudiante, $error);
    }

    /*
     * Callback. Chequea que el campo esté compuesto solo por letras y espacios (incluyendo acentos y ñ)
     */

    public function alpha_check($str) {
        if (preg_match('/^[\s\.a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/i', $str) > 0)
            return true;

        $this->form_validation->set_message('alpha_check', 'El campo %s contiene caracteres no permitidos');
        return false;
    }

    /*
     * Callback. Chequea que el campo esté compuesto solo por letras y espacios (incluyendo acentos y ñ) y algunos símbolos
     */

    public function alpha_sym_check($str) {
        if ($str == '' || preg_match('/^[\s\.0-9a-zA-ZáéíóúÁÉÍÓÚñÑ#%,"\'\/\-]+$/i', $str) > 0)
            return true;

        $this->form_validation->set_message('alpha_sym_check', 'El campo %s contiene caracteres no permitidos');
        return false;
    }

    /*
     * Callback. Chequea que la localidad se halla especificado
     */

    public function localidad_chk($str) {
        if ($str == 0) {
            $this->form_validation->set_message('localidad_chk', 'El campo %s es requerido');
            return FALSE;
        }

        return TRUE;
    }

    /*
     * Callback. Chequea si el carnet de identidad es válido
     */
    /*
      public function ci_check($str) {
      $y = 2000 + substr($str, 0, 2);
      $cy = $this->curso->activo_año();

      if ($y >= $cy) {
      $this->form_validation->set_message('ci_check', 'El CI no es válido porque el año es igual o mayor al año actual');
      return FALSE;
      }

      $edad = $cy - $y;
      if ($edad < 11 || $edad > 14) {
      $this->form_validation->set_message('ci_check', 'El CI no es válido porque la edad no está en el rango de 11 a 14 años');
      return FALSE;
      }

      $m = substr($str, 2, 2);
      $d = substr($str, 4, 2);

      $datetime = date_parse_from_format('Y-m-d', "{$y}-{$m}-{$d}");

      if ($datetime['warning_count'] > 0 || $datetime['error_count'] > 0) {
      $this->form_validation->set_message('ci_check', 'El campo %s no es válido porque la fecha no es válida');
      return FALSE;
      }

      return TRUE;
      }
     */
    /*
     * Se ejecuta al intentar eliminar un estudiante
     */

    function delete($idEstudiante, $idGrado, $idGrupo) {
        $result = $this->estudiante->eliminar($idEstudiante);

        if ($result === TRUE) {
            $this->session->set_flashdata('success', 'El estudiante ha sido eliminado con éxito.');
            $this->log->Insertar('Eliminó al estudiante con Id=' . $idEstudiante);
            redirect('estudiantes/show/' . $idGrado . '/' . $idGrupo);
            return;
        }

        if ($result === 1)
            $error = 'No se puede eliminar el estudiante porque tiene información de otros cursos.';
        else
            $error = 'No ha sido posible ' . (is_null($result) ? 'completar la operación' : 'eliminar el estudiante') . '.';

        $this->show($idGrado, $idGrupo, '', $idEstudiante, $error);
    }

    /*
     * Se ejecuta al intentar dar baja a un estudiante
     */

    function baja($idEstudiante, $idGrado, $idGrupo) {
        $error = '';

        if ($this->usuario->tiene_permiso(PERM_DAR_BAJA)) {
            $result = $this->estudiante->baja($idEstudiante, $this->input->post('observ'));

            if ($result === TRUE) {
                $this->session->set_flashdata('success', 'El estudiante ha sido dado de baja con éxito.');
                $this->log->Insertar('Dio baja al estudiante con Id=' . $idEstudiante);
                redirect('estudiantes/show/' . $idGrado . '/' . $idGrupo);
                return;
            }

            if ($result === 0)
                $error = 'No se puede dar baja al estudiante porque ya tiene otro estado.';
            else
                $error = 'No ha sido posible ' . (is_null($result) ? 'completar la operación' : 'dar baja el estudiante') . '.';
        }
        else
            $error = 'Usted no tiene permisos para bar baja.';

        $this->show($idGrado, $idGrupo, '', $idEstudiante, $error);
    }

    /*
     * Se ejecuta al intentar revertir la baja de un estudiante
     */

    function revertir_baja($idEstudiante, $idGrado, $idGrupo) {
        $error = '';

        if ($this->usuario->tiene_permiso(PERM_DAR_BAJA)) {
            $result = $this->estudiante->deshacer_baja($idEstudiante);

            if ($result === TRUE) {
                $this->session->set_flashdata('success', 'El estudiante ha dejado de ser baja.');
                $this->log->Insertar('Deshizo la baja del estudiante con Id=' . $idEstudiante);
                redirect('estudiantes/show/' . $idGrado . '/' . $idGrupo);
                return;
            }

            if ($result === 0)
                $error = 'El estudiante no existe o no es baja.';
            else
                $error = 'No ha sido posible ' . (is_null($result) ? 'completar la operación' : 'revertir la baja del estudiante') . '.';
        }
        else
            $error = 'Usted no tiene permisos para bar baja.';

        $this->show($idGrado, $idGrupo, '', $idEstudiante, $error);
    }

    /*
     * Se ejecuta al intentar cambiar de grupo a un estudiante
     */

    function cambiar_grupo($idEstudiante, $idGrado, $idGrupo) {
        $error = '';

        if ($this->input->post('idNuevoGrupo')) {
            $result = $this->estudiante->cambiar_grupo($idEstudiante, $this->input->post('idNuevoGrupo'));

            if ($result === TRUE) {
                $this->session->set_flashdata('success', 'El estudiante ha sido cambiado de grupo con éxito.');
                $this->log->Insertar('Cambió al grupo ' . $idGrado . '-' . $idGrupo . ' al estudiante con Id=' . $idEstudiante);
                redirect('estudiantes/show/' . $idGrado . '/' . $idGrupo);
                return;
            }

            if ($result === 0)
                $error = 'El grado del grupo de destino no coincide con el grado del estudiante.';
            elseif ($result === -1)
                $error = 'El estudiante no existe.';
            else
                $error = 'No ha sido posible ' . (is_null($result) ? 'completar la operación' : 'cambiar de grupo al estudiante') . '.';
        }

        $this->index($idGrado, $idGrupo, 'cambiar_grupo', $idEstudiante, $error);
    }

}