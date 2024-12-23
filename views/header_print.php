<!DOCTYPE html>
<?php
$res_base = base_url();
$baseURL = base_url() . 'index.php/';
$i = get_instance();
?>
<html lang="es">
    <head>
        <title>Control de matrícula</title>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <link rel="stylesheet" href="<?php echo $res_base; ?>resources/bootstrap/css/bootstrap.css" type="text/css" media="screen,print"/>
        <script src="<?php echo $res_base; ?>resources/js/jquery.min.js" type="text/javascript"></script>
        <script src="<?php echo $res_base; ?>resources/bootstrap/js/bootstrap.js" type="text/javascript"></script>
        <script src="<?php echo $res_base; ?>resources/bootstrap/js/bootbox.js" type="text/javascript"></script>
    </head>
    <body>

        <div class="container">
            <div class="row">
                <div class="span12">
                    <?php
                    if (!empty($titulo)):
                        ?>
                        <h3 style="margin: 0px"><?php echo $titulo; ?></h3>
                    <?php endif; ?>
                    <span class="btn-mini"><br/></span>
