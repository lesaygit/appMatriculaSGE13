<?php if ($error === -1): ?>
    <p class="text-info">El curso <?php echo get_instance()->curso->siguiente_str(); ?> ya se encuentra inicializado.</p>
    <p class="text-info">Ahora puede conformar los grupos del curso siguiente para
        luego procesar los estudiantes y posteriormente pasar al curso siguiente.</p>
    <?php
    return;
endif;
?>

<p class="text-info">Al inicializar el curso siguiente, se propicia la conformación de los grupos y el procesamiento de los
    estudiantes que pasan de grado, que repiten o que se gradúan.</p>

<p class="text-info">El mejor momento para hacer esta operación es al terminar el curso activo.</p>

<p class="text-info">Si está seguro que desea proceder, seleccione el botón Aceptar, de lo contrario seleccione el botón Cancelar.</p>

<div class="modal-footer">
    <input autocomplete="off" type="hidden" name="proceed" value="1" />
    <input type="button" class="btn" value="Cancelar" id="btnCancelar" />
    <input type="submit" class="btn btn-primary" value="Aceptar" />
</div>

<br/>
<?php
if (!empty($error))
    echo '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">x</button><h4 class="">Error</h4>' . $error . '</div>';
?>

<script type="text/javascript">
    $(window).load(function() {
        $('#btnCancelar').click(function(event) {
            event.preventDefault();
            document.location = '<?php echo base_url() . 'index.php/main'; ?>';
        });
    });
</script>