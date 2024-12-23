<!DOCTYPE html>
<?php
$res_base = base_url();
$baseURL = base_url() . 'index.php/';
?>
<html lang="es" dir="ltr">
    <head>
        <title>Autentificación</title>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <link rel="stylesheet" href="<?php echo $res_base; ?>resources/estilos.css" type="text/css" />
        <link rel="stylesheet" href="<?php echo $res_base; ?>resources/bootstrap/default/css/bootstrap.css" type="text/css" />
        <script src="<?php echo $res_base; ?>resources/js/jquery.min.js" type="text/javascript"></script>
        <script src="<?php echo $res_base; ?>resources/bootstrap/default/js/bootstrap.js" type="text/javascript"></script>
        <script src="<?php echo $res_base; ?>resources/bootstrap/default/js/bootbox.js" type="text/javascript"></script>
    </head>
    <body>

        <div class="container" id="container">
            <div class="row">
                <div class="span12" style="margin-right: 20px">
                    <div class="" style="margin-top: 20px; margin-bottom: 50px">
                        <h1 id="header_h1">
                            <small  id="header_small">Sistema de Gestión de</small><br/>&nbsp;&nbsp;&nbsp;Matrícula
                        </h1>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="offset2 span8">
                    <?php
                    echo form_open('', array('id' => 'globalForm'));
                    ?>
                    <span class="btn-mini"><br/></span>


