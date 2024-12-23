<?php $url_pref = base_url() . 'index.php/grupos/'; ?>
<table class="table table-condensed table-hover">
    <thead>
        <tr>
            <th width="16"></th>
            <th>Nombre del grupo</th>
            <th width="16"></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (empty($state))
            $state = '';

        $grupos['new'] = array('grado' => $grado, 'nombre' => '');

        $found = false;

        foreach ($grupos as $id => $data) {
            if ($id == $idGrupo && $state) {
                $found = true;
                $grado = $data['grado'];
                $nombre = $data['nombre'];
                $titulo = $id == 'new' ? 'Agregar grupo' : 'Actualizar grupo';
            }

            echo '<tr>';
            if ($id === 'new') {
                echo "<td><a href=\"{$url_pref}edit/new\" class=\"icon-plus\" title=\"Agregar grupo\" alt=\"new\"></a></td>";
                echo "<td colspan=\"2\" class=\"muted\">Agregar nuevo grupo</td>";
            } else {
                $cant = $data['cantEstud'];
                echo "<td><a href=\"{$url_pref}edit/{$id}\" class=\"icon-pencil\" title=\"Actualizar grupo\" alt=\"{$id}\"></a></td>";
                echo "<td>{$data['grado']}-{$data['nombre']}" . ($cant > 0 ? '<small style="margin-left: 30px">(' . $cant . ' estudiante' . ($cant > 1 ? 's' : '') . ')</small>' : '') . "</td>";
                if ($data['cantEstud'])
                    echo '<td>&nbsp;</td>';
                else
                    echo "<td><a href=\"#\" class=\"icon-remove\" title=\"Eliminar grupo\" alt=\"{$id}\"></a></td>";
            }
            echo '</tr>';
        }
        ?>
    </tbody>
</table>

<?php
if ($found):
    if ($v = @$_POST['grado'])
        $grado = $v;

    if ($v = trim(set_value('nombre')))
        $nombre = $v;
    ?>
    <div id="dialogContent">

        <div class="row-fluid">

            <div class="span5">
                <strong>Grado</strong><br/>
                <?php echo form_dropdown('grado', array('7' => 'Séptimo', '8' => 'Octavo', '9' => 'Noveno'), $grado, 'autocomplete="off" id="selGrados"'); ?>
            </div>

            <div class="span4">
                <strong>Número/Letra</strong><br/>
                <?php
                echo form_input(array(
                    'id' => 'inpNombre',
                    'name' => 'nombre',
                    'value' => html_entity_decode($nombre),
                    'maxlength' => '2',
                    'class' => 'input-medium'
                ));
                ?>
            </div>

            <div class="span3">
                <strong>Muestra</strong><br/>
                <?php
                echo form_input(array(
                    'id' => 'muestra',
                    'disabled' => 'disabled',
                    'class' => 'muted input-mini'
                ));
                ?>
            </div>

        </div>

        <?php
        if (!empty($error) || $error = validation_errors('<div class="">', '</div>'))
            echo '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">x</button><h4 class="">Error</h4>' . $error . '</div>';
        ?>
        <input autocomplete="off" type="hidden" name="idGrupo" value="<?php echo $idGrupo; ?>" />
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Aceptar</button>&nbsp;
            <input type="button" class="btn" id="btnCancel" value="Cancelar" />
        </div>

    </div>
<?php endif; ?>

<script type="text/javascript">
    function hideDialog() {
        document.location = '<?php echo $url_pref; ?>';
    }

    function updateMuestra() {
        $('#muestra').val($('#selGrados').val() + '-' + $('#inpNombre').val().toUpperCase());
    }

    $(window).load(function() {
<?php if ($found): ?>
            //Mostrar el diálogo con los controles del formulario
            bootbox.dialog($('#globalForm').clone().html('').append($('#dialogContent')), [], {
                "header": '<?php echo $titulo; ?>',
                "onEscape": hideDialog
            });

            $('input:first').focus(); //Poner el foco al primer input

            $('#inpNombre').keyup(updateMuestra);
            $('#selGrados').change(updateMuestra).change();

    <?php
else:
    if ($error)
        echo "bootbox.alert('" . quotes_to_entities(str_replace(array("\r", "\n"), array('', ''), $error), true) . "');";
endif;
?>

        //Establecer la funcionalidad de cualquier botón Cancelar
        $('#btnCancel').click(hideDialog);

        //Establecer la funcionalidad al botón Eliminar
        $('a.icon-remove').click(function(event) {
            event.preventDefault();

            var alt = $(this).attr('alt');

            bootbox.confirm('¿Está seguro que desea eliminar este grupo?', function(result) {
                if (result)
                    document.location = '<?php echo "{$url_pref}delete/"; ?>' + alt;
            });
        });
    });
</script>
