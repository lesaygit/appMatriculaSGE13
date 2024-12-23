<?php
$url_pref = base_url() . 'index.php/procesar/';
?>
<div class="row-fluid">
    <div class="span3">
        <strong>Grado</strong><br/>
        <?php echo form_dropdown('idGrado', array('7' => 'Séptimo', '8' => 'Octavo', '9' => 'Noveno'), $idGrado, 'id="idGrado" autocomplete="off" class="input-medium"'); ?>
    </div>

    <div class="span3">
        <strong>Grupo</strong><br/>
        <?php echo form_dropdown('idGrupo', array(), null, 'id="idGrupo" autocomplete="off" class="input-medium"'); ?>
    </div>

    <div class="span6">
        <br/>
        <input type="button" id="btnUpdate" class="btn" value="Actualizar" />
    </div>
</div>

<h5 class="text-info">Grupo actual: <?php echo $idGrado . '-' . $grupos[$idGrupo]['nombre']; ?></h5>

<table class="table table-condensed table-hover">
    <thead>
        <tr>
            <th>Nombre y apellidos</th>
            <th style="width: 25%">Estado</th>
            <th style="width: 25%">Grupo de destino</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (empty($estudiantes)) {
            echo '<tr><td colspan="3" class="text-info">No se han agregado estudiantes</td></tr>';
        } else {
            foreach ($estudiantes as $id => $data) {
                $baja = $data['estado'] == 1;

                //Estos datos nos van a servir para crear el dropdown del usuario con jQuery
                $extraData = "data-idestudiante=\"{$id}\" data-idgrado=\"{$idGrado}\" data-idgrupo=\"{$idGrupo}\" data-estado=\"{$data['estado']}\"";
                if ($baja)
                    $extraData .= ' data-baja="1"';

                echo '<tr' . ($baja ? ' class="muted" style="text-decoration: line-through" title="Motivo de la baja: ' . htmlentities($data['obs_baja']) . '"' : '') . '>';
                echo "<td style=\"vertical-align: middle\">{$data['nombre']} {$data['apellido1']} {$data['apellido2']}</td>";
                echo "<td class=\"estadoTD form-inline\" {$extraData}></td>";
                echo "<td style=\"vertical-align: middle\" class=\"form-inline\" id=\"grupoEstud{$id}\"></td>";
                echo '</tr>';
            }
        }
        ?>
    </tbody>
</table>
<?php
if (!empty($error))
    echo '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">x</button><h4 class="">Error</h4>' . $error . '</div>';
?>
<?php if (!empty($estudiantes)): ?>
    <div class="modal-footer">
        <input type="button" id="btnSave" class="btn btn-primary" value="Guardar cambios" />
    </div>
<?php endif; ?>

<script type="text/javascript">
    var estados = <?php echo json_encode($estados); ?>;
    var estadoEstud = <?php echo json_encode($estadoEstud); ?>;
    var grupoEstud = <?php echo json_encode($grupoEstud); ?>;
    var gruposSig = <?php echo json_encode($grupos_sig); ?>;

    $(window).load(function() {
        $('#globalForm').attr({'action': '<?php echo $url_pref; ?>'});

        //Armar el dropdown para cada estudiante
        $('.estadoTD').each(function(i, e) {
            var baja = $(e).data('baja');
            //Si es baja mostrar el estado deshabilitado
            if (baja) {
                var selEstados = $('<select disabled="disabled" class="muted input-medium"></select>');
                $('<option value="0">Baja</option>').appendTo(selEstados);
                $(selEstados).appendTo(e);
                return;
            }

            var idEstudiante = $(e).data('idestudiante');
            var idGrado = $(e).data('idgrado');
            var idGrupo = $(e).data('idgrupo');
            var estado = $(e).data('estado');

            //Agregar los estados
            var selEstados = $('<select name="estado[' + idEstudiante + ']" class="input-medium"></select>');
            $('<option value="0"></option>').appendTo(selEstados);
            $.each(estados, function(id, nombre) {
                if (id == 1 || (id == 2 && idGrado == 9) || (id == 3 && idGrado < 9))
                    return;
                var sel = id == (typeof(estadoEstud[idEstudiante]) == 'undefined' ? estado : estadoEstud[idEstudiante]);
                $('<option value="' + id + '"' + (sel ? ' selected="selected"' : '') + '>' + nombre + '</option>').appendTo(selEstados);
            });

            $(selEstados).appendTo(e).change(function() {
                //Si cambia un estado, activar el botón Guardar
                $('#btnSave').removeAttr('disabled');

                var val = $(this).val();
                var grpEstud = $('#grupoEstud' + idEstudiante);

                if (val == 0 || val == 3)
                    grpEstud.html('<span class="span12">-</span>');
                else {
                    //Hay que crear y mostrar el dropdown de los grupos de destino
                    var grupos = $('<select name="grupo[' + idEstudiante + ']" class="input-medium"></select>');

                    //Agregar todos los grupos de destino según el estado
                    $.each(gruposSig, function(id, grupo) {
                        if (val == 2 && grupo['grado'] != idGrado + 1)
                            return;
                        if (val == 4 && grupo['grado'] != idGrado)
                            return;
                        var sel = id == (typeof(grupoEstud[idEstudiante]) == 'undefined' ? '-' : grupoEstud[idEstudiante]);
                        $('<option value="' + id + '"' + (sel ? ' selected="selected"' : '') + '>' + grupo['grado'] + '-' + grupo['nombre'] + '</option>').appendTo(grupos);
                    });

                    grpEstud.html('');
                    $(grupos).appendTo(grpEstud).change(function() {
                        $('#btnSave').removeAttr('disabled'); //Si cambia un grupo de destino, activar el botón Guardar
                    });
                }

            }).change();
        });

        //Establecer la funcionalidad al botón Actualizar
        $('#btnUpdate').click(function() {
            var idGrupo = $('#idGrupo').val();
            if (!idGrupo)
                bootbox.alert('No se han definido grupos para el grado seleccionado.');
            else
                document.location = '<?php echo $url_pref; ?>index/' + $('#idGrado').val() + '/' + idGrupo;
        });

        //Establecer la funcionalidad al botón Guardar
        $('#btnSave').attr({'disabled': 'disabled'}).click(function() {
            $('#globalForm').attr({'action': '<?php echo $url_pref; ?>guardar/' + $('#idGrado').val() + '/' + $('#idGrupo').val()}).submit();
        });
    });
</script>
