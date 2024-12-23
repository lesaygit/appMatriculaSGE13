<!DOCTYPE html>
<?php
$res_base = base_url();
$baseURL = base_url() . 'index.php/';
$i = get_instance();

$tema = $i->session->userdata('tema');
if (!in_array($tema, $i->common_model->temas()))
    $tema = 'default';
?>
<html lang="es">
    <head>
        <title>Control de matrícula</title>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <link rel="stylesheet" href="<?php echo $res_base; ?>resources/estilos.css" type="text/css" />
        <link rel="stylesheet" href="<?php echo $res_base; ?>resources/bootstrap/<?php echo $tema; ?>/css/bootstrap.css" type="text/css" />
        <script src="<?php echo $res_base; ?>resources/js/jquery.min.js" type="text/javascript"></script>
        <script src="<?php echo $res_base; ?>resources/bootstrap/<?php echo $tema; ?>/js/bootstrap.js" type="text/javascript"></script>
        <script src="<?php echo $res_base; ?>resources/bootstrap/default/js/bootbox.js" type="text/javascript"></script>
    </head>
    <body>
        <?php
        //Si hubo éxito en alguna operación, mostrar el cuadro de éxito
        $success = $i->session->flashdata('success');

        if ($success) {
            ?>
            <div class="modal" id="successDiv" style="padding: 0px; top: 3px">
                <div style="margin: 10px" class="text-success">
                    <?php
                    if ($success === TRUE)
                        echo 'La operación ha sido satisfactoria';
                    else
                        echo $success;
                    ?>
                </div>
            </div>
            <?php
        }
        ?>

        <div class="container" id="container">
            <div class="row">
                <div class="span12" style="margin-right: 20px">
                    <div class="" style="margin-top: 20px; margin-bottom: 50px">
                        <div style="float: right">
                            <a class="btn-small" title="Inicio" href="<?php echo $baseURL; ?>main"><i class="icon-home"></i></a>
                            <a class="btn-small" title="Cerrar sesión" href="<?php echo $baseURL; ?>logout"><i class="icon-remove"></i></a>
                        </div>
                        <h1 id="header_h1">
                            <small  id="header_small">Sistema de Gestión de</small><br/>&nbsp;&nbsp;&nbsp;Matrícula
                        </h1>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="span3">

                    <?php
                    $items = $i->common_model->menu();

                    $curController = $i->uri->segments ? strtolower($i->uri->segments[1]) : 'main';

                    //Imprimir el menú
                    echo '<ul class="nav nav-list">';
                    $ii = 0;
                    foreach ($items as $menu => $subitems) {
                        if (is_array($subitems)) {
                            if ($ii++)
                                echo '<li class="divider"></li>';
                            echo "<li class=\"nav-header\">{$menu}</li>";
                            foreach ($subitems as $item => $controller) {
                                if ($item{0} == '*') {
                                    $item = substr($item, 1);
                                    echo '<li><a class="muted" href="javascript:void">' . $item . '</a></li>';
                                }
                                else
                                    echo '<li' . ($controller == $curController ? ' class="active"' : '') . "><a href=\"{$baseURL}{$controller}\">{$item}</a></li>";
                            }
                        }
                        //else {
                        //echo '<li' . ($subitems == $curController ? ' class="active"' : '') . "><a href=\"{$baseURL}{$subitems}\">{$menu}</a></li>";
                        //}
                    }
                    echo '</ul>';
                    ?>
                    <br/><br/><br/><br/>
                    <script type="text/javascript">
                        $(window).load(function() {
                            $('a.muted').click(function() {
                                $(this).blur();
                            });
                        });
                    </script>

                </div>
                <div class="span9">
                    <?php
                    echo form_open('', array('id' => 'globalForm'));
                    if (!empty($titulo)):
                        ?>
                        <h3><?php echo $titulo; ?></h3>
                    <?php endif; ?>

                    <div class="row-fluid">