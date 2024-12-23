<?php
$url_pref = base_url() . 'index.php/listado_grupo/';
extract($datos);
?>
<input autocomplete="off" type="hidden" id="inpShowBajas" value="<?php echo $mostrarBajas ? 1 : 0; ?>" />
<input autocomplete="off" type="hidden" id="inpPorApellidos" value="<?php echo $ordenarXApellidos ? 1 : 0; ?>" />
<input autocomplete="off" type="hidden" id="curCurso" value="<?php echo $idCurso; ?>" />

<?php if (!$imprimible): ?>
    <div class="row-fluid">
        <div class="span3">
            <strong>Curso</strong><br/>
            <?php echo form_dropdown('idCurso', $cursos, $idCurso, 'id="idCurso" autocomplete="off" class="input-medium"'); ?>
        </div>

        <div class="span3">
            <strong>Grado</strong><br/>
            <?php echo form_dropdown('idGrado', array('7' => 'Séptimo', '8' => 'Octavo', '9' => 'Noveno'), null, 'id="idGrado" autocomplete="off" class="input-medium"'); ?>
        </div>

        <div class="span3">
            <strong>Grupo</strong><br/>
            <?php echo form_dropdown('idGrupo', array(), null, 'id="idGrupo" autocomplete="off" class="input-medium"'); ?>
        </div>

        <div class="span2">
            <br/>
            <div class="btn-group">
                <button type="button" id="btnUpdate" class="btn">Actualizar</button>
                <button class="btn dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a id="showBajas" href="#"><i id="iconBajas"></i> Mostrar bajas</a></li>
                    <li><a id="porApellidos" href="#"><i id="iconPorApellidos"></i> Ordenar por apellidos</a></li>
                </ul>
            </div>
        </div>

    </div>

    <h5><a style="float:right" href="#" target="_blank" id="printView" rel="tooltip" data-title="Versión imprimible del grupo seleccionado"><i class="icon-print"></i> Versión imprimible</a></h5>
    <h5 class="text-info">Listado de estudiantes del grupo <?php echo $idGrado . '-' . $grupos[$idGrupo]['nombre'] . ' (Curso ' . $cursos[$idCurso] . ')'; ?></h5>
<?php endif; ?>

<table class="table table-condensed<?php if (!$imprimible) echo ' table-hover'; ?>">
    <thead>
        <tr>
            <th style="width: 20px">#</th>
            <th>Nombre(s) y apellidos</th>
            <th style="width: 100px">C. identidad</th>
            <th style="width: 80px">Sexo</th>
            <?php if (!$imprimible): ?>
                <th style="width: 20px"></th>
                <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        if (empty($estudiantes)) {
            echo '<tr><td colspan="5" class="text-info">No se han agregado estudiantes</td></tr>';
        } else {
            $num = 0;
            foreach ($estudiantes as $id => $data) {
                $baja = $data['estado'] == 1;
                if ($baja && !$mostrarBajas)
                    continue;

                $num++;

                echo '<tr' . ($baja ? ' class="muted" style="text-decoration: line-through" title="Motivo de la baja: ' . htmlentities($data['obs_baja']) . '"' : '') . '>';
                echo "<td>{$num}</td>";
                if ($ordenarXApellidos) {
                    echo "<td>{$data['apellido1']} {$data['apellido2']}, {$data['nombre']}</td>";
                } else {
                    echo "<td>{$data['nombre']} {$data['apellido1']} {$data['apellido2']}</td>";
                }
                echo "<td>{$data['CI']}</td>";
                echo '<td>' . ($data['sexo'] == 'M' ? 'Masculino' : 'Femenino') . '</td>';
                if (!$imprimible)
                    echo '<td><a href="' . base_url() . "index.php/detalle/index/{$id}\" target=\"_blank\" class=\"icon-tasks\" title=\"Ver detalles\" alt=\"{$id}\"></a></td>";
                echo '</tr>';
            }
        }
        ?>
    </tbody>
</table>

<script type="text/javascript">
    $(window).load(function() {
        //Poner o quitar la marca a la opción 'Mostrar bajas'
        function setBajaIcon() {
            if ($('#inpShowBajas').val() == 1)
                $('#iconBajas').removeClass('icon-white').addClass('icon-ok');
            else
                $('#iconBajas').removeClass('icon-ok').addClass('icon-white');
        }

        setBajaIcon(); //Inicializar la marca a la opción 'Mostrar bajas'

        //Cuando dé click en 'Mostrar bajas' cambiar el estado de la marca
        $('#showBajas').click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            $(this).blur();
            $('#inpShowBajas').val($('#inpShowBajas').val() == 1 ? 0 : 1);
            setBajaIcon();
        });

        //Poner o quitar la marca a la opción 'Ordenar por apellidos'
        function setPorApellidosIcon() {
            if ($('#inpPorApellidos').val() == 1)
                $('#iconPorApellidos').removeClass('icon-white').addClass('icon-ok');
            else
                $('#iconPorApellidos').removeClass('icon-ok').addClass('icon-white');
        }

        setPorApellidosIcon(); //Inicializar la marca a la opción 'Ordenar por apellidos'

        //Cuando dé click en 'Ordenar por apellidos' cambiar el estado de la marca
        $('#porApellidos').click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            $(this).blur();
            $('#inpPorApellidos').val($('#inpPorApellidos').val() == 1 ? 0 : 1);
            setPorApellidosIcon();
        });

        //Habilitar o deshabilitar el grado y el grupo cuando cambie el curso
        $('#idCurso').change(function() {
            var val = $(this).val();
            if (val == $('#curCurso').val())
                $('#idGrado, #idGrupo').removeAttr('disabled');
            else
                $('#idGrado, #idGrupo').attr({'disabled': 'disabled'});
        });

        //Cuando dé click en el botón actualizar actualizar la página con los datos del grupo/grado y las opciones
        $('#btnUpdate').click(function() {
            var idGrupo = $('#idGrupo').val();
            if (!idGrupo)
                bootbox.alert('No se han definido grupos para el grado seleccionado.');
            else
                document.location = '<?php echo $url_pref; ?>index/' + $('#idCurso').val() + '/' + $('#idGrado').val() + '/' + idGrupo + '/' + $('#inpShowBajas').val() + '/' + $('#inpPorApellidos').val();
        });

        //Cuando dé click en 'Versión imprimible'
        $('#printView').click(function(event) {
            var idGrupo = $('#idGrupo').val();
            if (!idGrupo) {
                event.preventDefault();
                bootbox.alert('No se han definido grupos para el grado seleccionado.');
            } else
                $(this).attr({'href': '<?php echo $url_pref; ?>printable/' + $('#idCurso').val() + '/' + $('#idGrado').val() + '/' + idGrupo + '/' + $('#inpShowBajas').val() + '/' + $('#inpPorApellidos').val()});
        });
    });
</script>
