<?php

class Listado_curso extends CI_Controller {

    private $imprimible = false;
    private $asPdf = false;

    public function __construct() {
        parent::__construct();

        $this->load->model('estudiante');
    }

    public function dbPanic($titulo, $mensaje = false) {
        $this->load->view($this->imprimible ? 'header_print' : 'header', array('titulo' => $titulo));
        if ($mensaje === false)
            $this->load->view('common/dberror');
        else
            $this->load->view('common/error', array('error' => $mensaje));
        $this->load->view($this->imprimible ? 'footer_print' : 'footer');
    }

    public function index($idCurso = null, $page = 1) {
        $cursos = $this->curso->lista(true);
        if (empty($cursos)) {
            if (is_null($cursos))
                $mensaje = false;
            else
                $mensaje = 'No se ha obtenido suficientes datos para mostrar el formulario.';

            $this->dbPanic('Listado de estudiantes', $mensaje);
            return;
        }

        //Comprobar si existe el curso
        if (!isset($cursos[$idCurso]))
            $idCurso = $this->curso->activo_id();

        if (!is_numeric($page) || $page < 1)
            $page = 1;

        //Obtener los estudiantes del curso activo
        $estudiantes = $this->estudiante->lista_curso($idCurso);
        if (!is_array($estudiantes)) {
            $this->dbPanic('Listado de estudiantes del curso ' . $cursos[$idCurso]);
            return;
        }

        //Preparar el paginador
        $this->load->library('pagination');

        $perPage = 20;
        $pagerConfig = $this->common_model->get_paginator_config(count($estudiantes), 'listado_curso/index/' . $idCurso . '/', 4, $perPage, $page, $offset);

        //Inicializar el paginador de CodeIgniter
        $this->pagination->initialize($pagerConfig);

        $datos = array(
            'page' => $page,
            'idCurso' => $idCurso,
            'cursos' => $cursos,
            'estudiantes' => $estudiantes,
            'imprimible' => $this->imprimible,
            'perPage' => $perPage,
            'offset' => $offset
        );

        if ($this->asPdf) {
            //$this->output->set_output('');
            $this->createPDF($datos);
            return;
        }

        if ($this->imprimible)
            $this->load->view('header_print', array('titulo' => 'Listado de estudiantes del curso ' . $cursos[$idCurso]));
        else
            $this->load->view('header', array('titulo' => 'Listado de estudiantes del curso ' . $cursos[$idCurso]));

        $this->load->view('listado_curso', $datos);

        $this->load->view($this->imprimible ? 'footer_print' : 'footer');
    }

    public function printable($idCurso = null, $page = 1) {
        $this->imprimible = true;
        $this->index($idCurso, $page);
    }

    public function pdf($idCurso = null, $page = 1) {
        $this->asPdf = true;
        $this->index($idCurso, $page);
    }

    public function createPDF($datos) {
        extract($datos);

        $this->load->library('pdf');

        $alumnos = array();

        $alumno = new stdClass();
        $alumno->paterno = 'Perez';
        $alumno->materno = 'Garcia';
        $alumno->nombre = 'Pepe';
        $alumno->fec_nac = '10/10/2000';
        $alumno->grado = '7';
        $alumno->grupo = 'B';
        
        $alumnos[] = $alumno;
        $alumnos[] = $alumno;
        $alumnos[] = $alumno;

        $pdf = new Pdf();
        
        $pdf->titulo = 'Listado de estudiantes del curso ' . $cursos[$idCurso].'  '.  utf8_decode(get_instance()->session_values->datosConfig['centro']);

        $pdf->AddPage();
        // Define el alias para el número de página que se imprimirá en el pie
        $pdf->AliasNbPages();

        // Se define el titulo, márgenes izquierdo, derecho y el color de relleno predeterminado
        $pdf->SetTitle("Listado de estudiantes");
        $pdf->SetLeftMargin(15);
        $pdf->SetRightMargin(15);
        $pdf->SetFillColor(200, 200, 200);

        $pdf->SetFont('Arial', 'B', 9);

        $pdf->Cell(10, 7, '#', 'TBL', 0, 'C', '1');
        $pdf->Cell(40, 7, 'Nombre', 'TB', 0, 'L', '1');
        $pdf->Cell(40, 7, 'Primer apellido', 'TB', 0, 'L', '1');
        $pdf->Cell(40, 7, 'Segundo apellido', 'TB', 0, 'L', '1');
        $pdf->Cell(30, 7, 'Carnet de I.', 'TB', 0, 'C', '1');
        $pdf->Cell(20, 7, 'Sexo', 'TBR', 0, 'L', '1');
        $pdf->Ln(7);

        $pdf->SetFillColor(230, 230, 230);
        
        $x = 1;
        $algunaBaja = false;
        foreach ($estudiantes as $estud) {
            if ($estud['estado'] == 1) {
                $algunaBaja = true;
                $pdf->SetTextColor(255, 0, 0);
            } else
                $pdf->SetTextColor(0, 0, 0);
                
            // Se imprimen los datos de cada estudiante
            $pdf->Cell(10, 5, $x++, 'BL', 0, 'C', 0);
            $pdf->Cell(40, 5, utf8_decode($estud['nombre']), 'B', 0, 'L', 0);
            $pdf->Cell(40, 5, utf8_decode($estud['apellido1']), 'B', 0, 'L', 0);
            $pdf->Cell(40, 5, utf8_decode($estud['apellido2']), 'B', 0, 'L', 0);
            $pdf->Cell(30, 5, $estud['CI'], 'B', 0, 'C', 0);
            $pdf->Cell(20, 5, $estud['sexo'] == 'M' ? 'Masculino' : 'Femenino', 'BR', 0, 'L', 0);

            //Se agrega un salto de linea
            $pdf->Ln(5);
        }
        
        if ($algunaBaja) {
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(10, 5, 'Los estudiantes marcados en rojo causaron baja.', '', 0, 'L', 0);
        }
        
        $pdf->SetTextColor(0, 0, 0);

        $pdf->Output("Estudiantes curso {$cursos[$idCurso]}.pdf", 'D');
    }

}