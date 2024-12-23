<?php

class View_log extends CI_Controller {

    private $imprimible = false;

    public function index($idUsuario = null, $ascendente = 1, $page = 1) {
        $usuarios = $this->usuario->lista();
        if (is_null($usuarios)) {
            $this->load->view($this->imprimible ? 'header_print' : 'header', array('titulo' => 'Trazas de usuario'));
            $this->load->view('common/dberror');
            $this->load->view($this->imprimible ? 'footer_print' : 'footer');
            return;
        }

        if (!isset($usuarios[$idUsuario]))
            $idUsuario = key($usuarios);

        if ($ascendente != 1 && $ascendente != 0)
            $ascendente = 1;
        
        $cantidadTotal = $this->log->CantidadTrazas($idUsuario);
        if (is_null($cantidadTotal)) {
            $this->load->view($this->imprimible ? 'header_print' : 'header', array('titulo' => 'Trazas de usuario'));
            $this->load->view('common/dberror');
            $this->load->view($this->imprimible ? 'footer_print' : 'footer');
            return;
        }

        $nombre = $usuarios[$idUsuario]['nombre'] . ' ' . $usuarios[$idUsuario]['apellidos'];

        //Preparar el paginador
        $this->load->library('pagination');

        if (!is_numeric($page) || $page < 1)
            $page = 1;

        $perPage = 20;
        $pagerConfig = $this->common_model->get_paginator_config($cantidadTotal, 'view_log/index/' . $idUsuario . '/' . $ascendente . '/', 5, $perPage, $page, $offset);

        //Inicializar el paginador de CodeIgniter
        $this->pagination->initialize($pagerConfig);
        
        $trazas = $this->log->Lista($idUsuario, $offset, $perPage, $ascendente);

        $datos = array(
            'idUsuario' => $idUsuario,
            'ascendente' => $ascendente,
            'usuarios' => $usuarios,
            'trazas' => $trazas,
            'imprimible' => $this->imprimible,
            'page' => $page
        );

        if ($this->imprimible)
            $this->load->view('header_print', array('titulo' => 'Trazas del usuario ' . $nombre));
        else
            $this->load->view('header', array('titulo' => 'Trazas del usuario ' . $nombre));

        $this->load->view('view_log', $datos);

        $this->load->view($this->imprimible ? 'footer_print' : 'footer');
    }

    public function printable($idUsuario = null, $ascendente = 1, $page = 0) {
        $this->imprimible = true;
        $this->index($idUsuario, $ascendente, $page);
    }

}