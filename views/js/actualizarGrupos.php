<script type="text/javascript">
    var grupos = <?php echo json_encode($grupos); ?>;

    //Establecer la funcionalidad para cuando cambia el grado
    $(window).load(function() {
        $('#idGrado').change(function() {
            var curGrado = $(this).val();

            //Eliminar las opciones actuales
            $('#idGrupo').html('');

            //Agregar los grupos
            $.each(grupos, function(id, grupo) {
                if (grupo['grado'] == curGrado)
                    $('<option value="' + id + '"' + (id == '<?php echo $idGrupo; ?>' ? ' selected="selected"' : '') + '>' + grupo['grado'] + '-' + grupo['nombre'] + '</option>').appendTo('#idGrupo');
            });

<?php if (!empty($extra)) echo $extra; ?>
        }).val(<?php echo $idGrado; ?>).change(); //Ejecutar la primera vez
    });
</script>

