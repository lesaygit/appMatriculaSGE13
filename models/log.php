<?php

class Log extends CI_Model {
    /*
     * Inserta un registro en la tabla logs
     */

    public function Insertar($texto) {
        $fecha = date('Ymd');
        $hora = date('Gis');
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($ip == '::1')
            $ip = '127.0.0.1';
        $idUsuario = $this->session_values->idUsuario;

        $this->db->insert('logs', array('fecha' => $fecha, 'hora' => $hora, 'idusuario' => $idUsuario, 'ip' => $ip, 'descripcion' => $texto));
    }

    /*
     * Devuelve la cantidad de registros que tiene un usuario en la tabla logs
     */

    public function CantidadTrazas($idUsuario) {
        $res = $this->db->query('SELECT count(*) as cant FROM logs WHERE idusuario = ?', array($idUsuario));
        if ($res === FALSE)
            return null;

        return $res->row()->cant;
    }

    /*
     * Devuelve las $cantidad trazas de un usuario, desde la posición $desde
     */

    public function Lista($idUsuario, $desde, $cantidad, $ascendente = true) {
        $order = $ascendente ? 'fecha asc, hora asc' : 'fecha desc, hora desc';
        /* if ($ascendente)
          $order = 'fecha asc, hora asc';
          else
          $order = 'fecha desc, hora desc'; */
        $this->db->select()->from('logs')->where('idusuario', $idUsuario)->order_by($order)->limit($cantidad, $desde);

        $res = $this->db->get();
        if (!$res)
            return array();

        $r = array();
        foreach ($res->result() as $row)
            $r[] = $row;

        return $r;
    }

}

?>