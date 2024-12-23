<?php
$url_pref = base_url() . 'index.php/buscar/';

extract($datos);

$i = get_instance();
$curso = $i->input->post('curso');
$field = $i->input->post('field');
$texto = html_entity_decode($i->input->post('texto'));
?>
<input autocomplete="off" type="hidden" id="likeType" name="likeType" value="<?php echo $likeType; ?>" />
<input autocomplete="off" type="hidden" id="pageNum" name="page" value="<?php echo $page; ?>" />

<div class="form-inline form-fluid">
    <div class="span3">
        <strong>Del curso</strong><br/>
        <?php echo form_dropdown('curso', array(1 => 'Curso activo', 2 => 'Cualquier curso'), $curso, 'id="curso" autocomplete="off" class="input-medium"'); ?>
    </div>

    <div class="span3">
        <strong>Buscar</strong><br/>
        <?php echo form_dropdown('field', $findFields, $field, 'id="field" autocomplete="off" class="input-medium"'); ?>
    </div>

    <div class="span4">
        <strong id="findText">Texto a buscar</strong><br/>
        <div class="btn-group">
            <div class="input-prepend">
                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a id="likeEquals" href="#"><i id="iconEquals"></i> Igual a...</a></li>
                    <li><a id="likeStarts" href="#"><i id="iconStarts"></i> Que comience con...</a></li>
                    <li><a id="likeContains" href="#"><i id="iconContains"></i> Que contenga...</a></li>
                    <li><a id="likeEnds" href="#"><i id="iconEnds"></i> Que termine con...</a></li>
                </ul>
                <?php
                echo form_input(array(
                    'name' => 'texto',
                    'value' => $texto,
                    'maxlength' => '20',
                    'class' => 'input-medium',
                    'id' => 'texto'
                ));
                ?>
            </div>
        </div>
    </div>

    <div class="span2">
        <br/>
        <button type="button" id="btnUpdate" class="btn">Actualizar</button>
    </div>
</div>

<?php if ($estudiantes): ?>
    <h5 class="text-info">
        &nbsp;<br/>
        <?php
        $f = array(
            1 => 'cuyo carnet de identidad',
            2 => 'cuyo nombre',
            3 => 'cuyo primer apellido',
            4 => 'cuyo segundo apellido',
            5 => 'que al menos un apellido',
            6 => 'que el nombre o los apellidos'
        );

        $plural = $field == 6;

        $s = 'Estudiantes ';
        if ($curso == 1)
            $s.= 'del curso activo ';
        $s .= $f[$field];

        switch ($likeType) {
            case 0: 
                $s.= $plural ? ' son iguales a' : ' es igual a';
                break;
            case 1:
                $s.= ' comienza' . ($plural ? 'n' : '') . ' con';
                break;
            case 2:
                $s .= ' contiene' . ($plural ? 'n' : '');
                break;
            case 3:
                $s .= $plural ? ' terminan con' : ' termina con';
                break;
        }

        $s .= ': ' . $texto;
        echo $s;
        ?>
    </h5>
    <table class="table table-condensed table-hover">
        <thead>
            <tr>
                <th style="width: 100px">C. identidad</th>
                <th>Nombre(s) y apellidos</th>
                <th style="width: 80px">Sexo</th>
                <th style="width: 20px"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $o = 0;
            $c = 0;
            foreach ($estudiantes as $id => $data) {
                if ($o++ < $offset)
                    continue;
                if ($c++ == $perPage)
                    break;

                echo '<tr>';
                echo "<td>{$data['CI']}</td>";
                echo "<td>{$data['nombre']} {$data['apellido1']} {$data['apellido2']}</td>";
                echo '<td>' . ($data['sexo'] == 'M' ? 'Masculino' : 'Femenino') . '</td>';
                echo '<td><a href="' . base_url() . "index.php/detalle/index/{$id}\" target=\"_blank\" class=\"icon-tasks\" title=\"Ver detalles\" alt=\"{$id}\"></a></td>";
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
    <?php
    echo $i->pagination->create_links();

elseif ($field && $texto):
    ?>
    &nbsp;<br/>
    <p class="text-info">No se han encontrado estudiantes con los datos especificados.</p>
<?php endif; ?>

<script type="text/javascript">
    $(window).load(function() {
        var icons = ['#iconEquals', '#iconStarts', '#iconContains', '#iconEnds'];
        var options = ['#likeEquals', '#likeStarts', '#likeContains', '#likeEnds'];

        //Mostrar la marca solo para la opción seleccionada
        function setIcons() {
            var val = $('#likeType').val();
            $.each(icons, function(i, id) {
                if (val == i) {
                    $(id).addClass('icon-ok').removeClass('icon-white');
                    $('#findText').text($(options[i] + ':parent').text());
                } else
                    $(id).addClass('icon-white').removeClass('icon-ok');
            });
        }

        setIcons(); //Inicializar la marca a las opciones

        //Cuando actualice, mostrar siempre primera página del paginador
        $('#btnUpdate').click(function() {
            $('#pageNum').val('0');
            $('#globalForm').attr({'action': '<?php echo $url_pref; ?>index/1'}).submit();
        });

        //Ajustar la marca y actualizar el campo oculto cuando se haga clic en cualquiera de las opciones
        $('#likeStarts, #likeContains, #likeEquals, #likeEnds').click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            $(this).blur();
            $('#likeType').val(options.indexOf('#' + $(this).attr('id')));
            setIcons();
        });

        //Si se muestra el paginador, arreglar los enlaces para que hagan POST y no GET
        $('.my_pager').click(function(event) {
            event.preventDefault();
            var href = $(this).attr('href');
            var pieces = href.split('\/');
            var page = pieces[pieces.length - 1];
            $('#pageNum').val(page);
            $('#globalForm').attr({'action': '<?php echo $url_pref; ?>index/' + page}).submit();
        });
    });
</script>