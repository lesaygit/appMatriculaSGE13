<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

//Chequear si está autentificado cada vez que se ejecuta una página
$hook['pre_controller'] = array(
    'class' => 'Access_checker',
    'function' => 'index',
    'filename' => 'access_checker.php',
    'filepath' => 'hooks',
);

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */