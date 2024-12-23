<?php if ($error === -1): ?>
    <p class="text-info">Todavía no están creadas las condiciones para activar el curso siguiente.</p>
    <p class="text-info">Las causas posibles son:</p>
    <ul class="text-info">
        <li>No se ha inicializado el curso siguiente.</li>
        <li>Existen grupos vacíos en el curso activo.</li>
        <li>No se ha definido grupos para los tres grados.</li>
        <li>No se han procesado todos los estudiantes del curso activo.</li>
    </ul>
    <?php
    return;
endif;
?>

<p class="text-info">Al activar el curso siguiente se está dando a conocer que se ha terminado el curso y se han procesado
    todos los estudiantes del curso activo.</p>

<p class="text-info">Esta acción no podrá deshacerse. Solo active el año si está seguro de lo que está haciendo.</p>

<p class="text-info">Si está seguro que desea proceder seleccione el botón Aceptar, de lo contrario seleccione el botón Cancelar.</p>

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