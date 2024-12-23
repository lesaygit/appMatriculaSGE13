<script type="text/javascript">
    var provs = <?php echo json_encode($provincias); ?>;
    var muns = <?php echo json_encode($municipios); ?>;

    //Establecer la funcionalidad para cuando cambia la provincia
    $(window).load(function() {
        $.each(provs, function(id, prov) {
            $('<option value="' + id + '">' + prov + '</option>').appendTo('#idProvincia');
        });
        
        $('#idProvincia').val('<?php echo @$_POST['idProvincia']; ?>');

        $('#idProvincia').change(function() {
            var curMuns = muns[$(this).val()];

            //Eliminar las opciones actuales
            $('#idMunicipio').html('');

            //Agregar los municipios de la provincia seleccionada
            $.each(curMuns, function(id, mun) {
                $('<option value="' + mun[0] + '">' + mun[1] + '</option>').appendTo('#idMunicipio');
            });

            <?php if (!empty($extra)) echo $extra; ?>
        }).change(); //Ejecutar la primera vez
        
        $('#idMunicipio').val('<?php echo @$_POST['idMunicipio']; ?>');
    });
</script>

