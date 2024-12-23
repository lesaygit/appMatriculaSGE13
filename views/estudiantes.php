<?php
$url_pref = base_url() . 'index.php/estudiantes/';
extract($datos);
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

    <div class="span3">
        <br/>
        <input type="button" id="btnUpdate" class="btn btn-primary" value="Actualizar" />
    </div>

    <div class="span3" style="text-align: right">
        <br/>
        <input type="button" id="addEstud" class="btn" value="Agregar estudiante" />
    </div>
</div>

<h5 class="text-info">Grupo actual: <?php echo $idGrado . '-' . $grupos[$idGrupo]['nombre']; ?></h5>

<table class="table table-condensed table-hover">
    <thead>
        <tr>
            <th width="16"></th>
            <th>C. Identidad</th>
            <th>Nombre(s)</th>
            <th>Primer apellido</th>
            <th>Segundo apellido</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (empty($state))
            $state = '';

        $found = false;

        if (empty($estudiantes)) {
            echo '<tr><td></td><td colspan="5" class="text-info">No se han agregado estudiantes</td></tr>';
        } else {
            foreach ($estudiantes as $id => $data) {
                if ($state && $id == $idEstudiante) {
                    $found = true;
                    $estud = $data;
                }

                $baja = $data['estado'] == 1;

                //Estos datos nos van a servir para crear el dropdown del usuario con jQuery
                $extraData = "data-idestudiante=\"{$id}\" data-idgrado=\"{$idGrado}\" data-idgrupo=\"{$idGrupo}\"";
                if ($baja)
                    $extraData .= ' data-baja="1"';

                echo '<tr' . ($baja ? ' class="muted" style="text-decoration: line-through" title="Motivo de la baja: ' . htmlentities($data['obs_baja']) . '"' : '') . '>';
                echo "<td {$extraData} class=\"dropdownTD\"></td>";
                echo "<td>{$data['CI']}</td>";
                echo "<td>{$data['nombre']}</td>";
                echo "<td>{$data['apellido1']}</td>";
                echo "<td>{$data['apellido2']}</td>";
                echo '</tr>';
            }
        }
        ?>
    </tbody>
</table>

<?php
switch ($state) {
    case 'edit':
        if ($idEstudiante == 'new') {
            $found = true;
            $estud = array(
                'CI' => '', 'nombre' => '', 'apellido1' => '', 'apellido2' => '',
                'direccion' => '', 'localidad' => '', 'color_ojos' => '',
                'color_pelo' => '', 'color_piel' => '', 'talla' => '', 'peso' => '', 'sexo' => '');

            $titulo = 'Agregar estudiante al grupo ' . $idGrado . '-' . $grupos[$idGrupo]['nombre'];
        }
        else
            $titulo = 'Actualizar estudiante';
        break;

    case 'cambiar_grupo':
        $titulo = 'Cambiar de grupo';
        break;

    default:
        $titulo = 'Actualizar estudiante';
        break;
}

if ($found) {
    //Extraer en variables los campos y si existe en el POST sobreescribir su valor
    foreach ($estud as $field => $value)
        $$field = ($v = @$_POST[$field]) ? $v : $value;

    echo '<div id="dialogContent">';

    echo "<input autocomplete=\"off\" type=\"hidden\" value=\"{$idGrado}\" name=\"idGrado\" />";
    echo "<input autocomplete=\"off\" type=\"hidden\" value=\"{$idGrupo}\" name=\"idGrupo\" />";
    echo "<input autocomplete=\"off\" type=\"hidden\" value=\"{$idEstudiante}\" name=\"idEstudiante\" />";

    if (!empty($error) || $error = validation_errors('<div class="">', '</div>'))
        echo '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">x</button><h4 class="">Error</h4>' . $error . '</div>';
    
    if ($state == 'cambiar_grupo') {
        //Cambiar de grupo
        echo '<p class="text-info"><strong>Estudiante</strong>: ' . $nombre . ' ' . $apellido1 . ' ' . $apellido2 . '</p>';
        $gruposGrado = array();
        foreach ($grupos as $id => $grupo)
            if ($grupo['grado'] == $idgrado && $id != $idgrupo)
                $gruposGrado[$id] = $idgrado . '-' . $grupo['nombre'];

        echo '<strong>Grupo</strong><br/>';
        echo form_dropdown('idNuevoGrupo', $gruposGrado, get_instance()->input->post('idNuevoGrupo'), 'class="input-medium"');
    } else {
        //Editar o insertar
        $cOjos = array_merge(array(0 => '-No se conoce-'), $cOjos);
        $cPiel = array_merge(array(0 => '-No se conoce-'), $cPiel);
        $cPelo = array_merge(array(0 => '-No se conoce-'), $cPelo);
        ?>
        <div class="row-fluid">
            <div class="span4">
                <strong>Nombre(s)</strong><br/>
                <?php
                echo form_input(array(
                    'name' => 'nombre',
                    'value' => html_entity_decode($nombre),
                    'maxlength' => '25',
                    'class' => 'input-medium',
                    'placeholder' => 'Escriba el nombre'
                ));
                ?>
            </div>
            <div class="span4">
                <strong>Primer apellido</strong><br/>
                <?php
                echo form_input(array(
                    'name' => 'apellido1',
                    'value' => html_entity_decode($apellido1),
                    'maxlength' => '15',
                    'class' => 'input-medium',
                    'placeholder' => 'Primer apellido'
                ));
                ?>
            </div>
            <div class="span4">
                <strong>Segundo apellido</strong><br/>
                <?php
                echo form_input(array(
                    'name' => 'apellido2',
                    'value' => html_entity_decode($apellido2),
                    'maxlength' => '15',
                    'class' => 'input-medium',
                    'placeholder' => 'Segundo apellido'
                ));
                ?>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span4">
                <strong>Carnet de Identidad</strong><br/>
                <?php
                echo form_input(array(
                    'name' => 'CI',
                    'value' => html_entity_decode($CI),
                    'maxlength' => '11',
                    'class' => 'input-medium',
                    'placeholder' => 'Carnet de identidad'
                ));
                ?>
            </div>
            <div class="span8">
                <strong>Sexo</strong><br/>
                <?php echo form_dropdown('sexo', array('M' => 'Masculino', 'F' => 'Femenino'), $sexo, 'class="input-medium"'); ?>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span7">
                Dirección<br/>
                <?php
                echo form_input(array(
                    'name' => 'direccion',
                    'value' => html_entity_decode($direccion),
                    'maxlength' => '60',
                    'class' => 'input-xlarge'
                ));
                ?>
            </div>
            <div class="span5">
                <strong>Localidad</strong><br/>
                <?php echo form_dropdown('localidad', $localidades, $localidad, 'class="input-medium"'); ?>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span4">
                Color de la piel<br/>
                <?php echo form_dropdown('color_piel', $cPiel, $color_piel, 'class="input-medium"'); ?>
            </div>
            <div class="span4">
                Color de los ojos<br/>
                <?php echo form_dropdown('color_ojos', $cOjos, $color_ojos, 'class="input-medium"'); ?>
            </div>
            <div class="span4">
                Color del pelo<br/>
                <?php echo form_dropdown('color_pelo', $cPelo, $color_pelo, 'class="input-medium"'); ?>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span2">
                Talla (cm)<br/>
                <?php
                echo form_input(array(
                    'name' => 'talla',
                    'value' => html_entity_decode($talla),
                    'maxlength' => '3',
                    'class' => 'input-mini'
                ));
                ?>
            </div>
            <div class="span10">
                Peso (Kg)<br/>
                <?php
                echo form_input(array(
                    'name' => 'peso',
                    'value' => html_entity_decode($peso),
                    'maxlength' => '3',
                    'class' => 'input-mini'
                ));
                ?>
            </div>
        </div>
        <?php
    }

    echo '</div>';
}
?>

<!-- Formulario para dar baja. Se envía oculto y se muestra con javascript en un cuadro de diálogo -->
<div id="bajaContent" style="display: none">
    ¿Está seguro que desea dar baja a este estudiante?<br/><br/>
    <strong>Motivo de la baja</strong><br/>
    <input type="text" id="observ" name="observ" class="input-xlarge" placeholder="Escriba el motivo de la baja" />
    <small class="text-error" id="bajaError" style="display: none">
        &nbsp;Especifique el motivo de la baja.
    </small>
    <div class="modal-footer" id="modalFooter">
        <a id="btnBajaCancelar" href="javascript:;" class="btn null" data-handler="0">Cancelar</a>
        <a id="btnBajaAceptar" href="javascript:;" class="btn btn-primary" data-handler="1">Aceptar</a>
    </div>
</div>

<script type="text/javascript">
    function reloadPage() {
        document.location = '<?php echo $url_pref . 'show/' . $idGrado . '/' . $idGrupo; ?>';
    }

    $(window).load(function() {
        //Actualizar el ACTION del formulario principal
        $('#globalForm').attr({'action': '<?php echo $url_pref; ?>'});

        //Armar la lista desplegable (dropdown) para cada estudiante
        $('.dropdownTD').each(function(i, e) {
            //Obtener los datos del estudiante que están en atributos data-xxxx
            var baja = $(e).data('baja');
            var idEstudiante = $(e).data('idestudiante');
            var idGrado = $(e).data('idgrado');
            var idGrupo = $(e).data('idgrupo');

            var btnGrp = $('<div class="btn-group"><a href="#" data-toggle="dropdown" class="btn btn-small dropdown-toggle"><span class="caret"></span></a></div>');
            var ul = $('<ul class="dropdown-menu"></ul>');
            $(ul).appendTo(btnGrp);
            $(btnGrp).appendTo(e);

            //Esta función crea una opción y la añade al UL creado anteriormente
            function crearLI(href, icon, txt, onClick) {
                var li = $('<li></li>');
                var a = $('<a href="' + href + '" class="btn-small btn-link"><span class="icon-' + icon + '"></span>&nbsp;&nbsp;' + txt + '</a>');

                if (typeof(onClick) == 'function')
                    $(a).click(onClick);

                $(a).appendTo(li);  //Añadir el A al LI
                $(li).appendTo(ul); //Añadir el LI al UL
            }

            if (baja) {
                //Actualizar
                crearLI('<?php echo $url_pref; ?>edit/' + idEstudiante + '/' + idGrado + '/' + idGrupo, 'pencil', 'Actualizar datos');

<?php if (get_instance()->usuario->tiene_permiso(PERM_DAR_BAJA)): ?>
                    //Revertir la baja
                    crearLI('#', 'refresh', 'Revertir baja', function(event) {
                        event.preventDefault();
                        bootbox.confirm('<strong>¿Está seguro que desea deshacer la baja de este estudiante?</strong><br /><br /><p class="text-info">Esto hará que el estudiante vuelva a estar matriculado en el mismo grupo donde se encontraba.</p>', function(result) {
                            if (result)
                                document.location = '<?php echo $url_pref; ?>revertir_baja/' + idEstudiante + '/' + idGrado + '/' + idGrupo;
                        });
                    });
<?php endif; ?>
            } else { //El estudiante no es baja
                //Actualizar
                crearLI('<?php echo $url_pref; ?>edit/' + idEstudiante + '/' + idGrado + '/' + idGrupo, 'pencil', 'Actualizar datos');

                //Cambiar de grupo
                //Contar la cantidad de grupos del mismo grado distintos al grupo del estudiante
                grpCount = 0;
                $.each(grupos, function(id, grupo) {
                    if (grupo['grado'] == idGrado && id != idGrupo)
                        grpCount++;
                });

                if (grpCount > 0) {
                    //Hay al menos otro grupo, se puede mostrar la opción
                    crearLI('#', 'move', 'Cambiar de grupo', function(event) {
                        event.preventDefault();
                        document.location = '<?php echo $url_pref; ?>cambiar_grupo/' + idEstudiante + '/' + idGrado + '/' + idGrupo;
                    });
                }

                $('<li class="divider"></li>').appendTo(ul);

<?php if (get_instance()->usuario->tiene_permiso(PERM_DAR_BAJA)): ?>
                    //Dar baja
                    crearLI('#', 'remove-sign', 'Dar baja', function(event) {
                        event.preventDefault();

                        //Crear un formulario
                        var form = $('<?php echo form_open('', 'id="formBaja"'); ?></form>');
                        //añadirle los controles del formulario de baja
                        $('#bajaContent').show().appendTo(form);
                        //y actualizar su ACTION
                        $(form).attr({'action': '<?php echo $url_pref; ?>baja/' + idEstudiante + '/' + idGrado + '/' + idGrupo});
                        //mostrar el cuadro de diálogo con el formulario como contenido
                        bootbox.dialog(form, [], {"header": 'Dar baja'});

                        //Arreglar el footer del cuadro de diálogo, porque no está donde debe estar
                        $('#modalFooter').appendTo($(form).parent().parent());

                        //Ajustar la funcionalidad del botón cerrar (X) para que recargue la página
                        $('a.close').click(function(event) {
                            event.preventDefault();
                            reloadPage();
                        });

                        //Establecer la funcionalidad al botón Cancelar
                        $('#btnBajaCancelar').click(reloadPage);

                        //Establecer la funcionalidad al botón Aceptar
                        $('#btnBajaAceptar').click(function() {
                            if ($('#observ').val().trim())
                                $(form).submit();
                            else
                                $('#bajaError').show('slow');
                        });

                    });

                    $('<li class="divider"></li>').appendTo(ul);
<?php endif; ?>
                //Eliminar
                crearLI('#', 'remove', 'Eliminar', function(event) {
                    event.preventDefault();
                    bootbox.confirm('¿Está seguro que desea eliminar este estudiante?', function(result) {
                        if (result)
                            document.location = '<?php echo $url_pref; ?>delete/' + idEstudiante + '/' + idGrado + '/' + idGrupo;
                    });
                });
            }
        });

        //Funcionalidad del botón Actualizar
        $('#btnUpdate').click(function() {
            var idGrupo = $('#idGrupo').val();
            if (!idGrupo)
                bootbox.alert('No se han definido grupos para el grado seleccionado.');
            else
                document.location = '<?php echo $url_pref; ?>show/' + $('#idGrado').val() + '/' + idGrupo;
        });

        //Funcionalidad del botón Agregar estudiante
        $('#addEstud').click(function(event) {
            event.preventDefault();
            var idGrupo = $('#idGrupo').val();
            if (!idGrupo) {
                bootbox.alert('No se han definido grupos para el grado seleccionado.');
                return;
            }

            document.location = '<?php echo $url_pref; ?>edit/new/' + $('#idGrado').val() + '/' + idGrupo;
        });

<?php if ($found): ?>
            //Se está actualizando o agregando. Mostrar el diálogo con los controles del formulario
            bootbox.dialog($('<?php echo form_open($url_pref . $state . '/' . $idEstudiante . '/' . $idGrado . '/' . $idGrupo, 'id="subForm"'); ?></form>')
                    .append($('#dialogContent')),
                    [{
                            "label": 'Cancelar',
                            "icon": {CANCEL: 1},
                            "callback": reloadPage
                        },
                        {
                            "label": 'Aceptar',
                            "icon": {OK: 1},
                            "callback": function() {
                                $('#subForm').submit();
                            }
                        }],
            {
                "header": '<?php echo $titulo; ?>',
                "onEscape": reloadPage,
            });

            //Cuando se cierre por el botón X (Cerrar), hacer lo mismo que el botón Cancelar
            $('a.close').click(function(event) {
                event.preventDefault();
                reloadPage();
            });

            //Poner el foco al primer input
            $('#dialogContent input[type!="hidden"]:first').focus();
    <?php
else:
    if ($error)
        echo "bootbox.alert('" . quotes_to_entities(str_replace(array("\r", "\n"), array('', ''), $error), true) . "');";
endif;
?>

    });
</script>

<br/><br/><br/><br/><br/><br/><br/><br/>
