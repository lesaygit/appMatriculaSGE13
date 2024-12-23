<?php
$url_pref = base_url() . 'index.php/graduados/';
extract($datos);

if (!$imprimible):
    ?>
    <input autocomplete = "off" type = "hidden" id = "inpPorApellidos" value = "<?php echo $ordenarXApellidos ? 1 : 0; ?>" />

    <div class="row-fluid">
        <div class="span3">
            <strong>Curso</strong><br/>
            <?php echo form_dropdown('idCurso', $cursos, $idCurso, 'id="idCurso" autocomplete="off" class="input-medium"'); ?>
        </div>

        <div class="span9"><br/>
            <div class="btn-group">
                <button type="button" id="btnUpdate" class="btn">Actualizar</button>
                <button class="btn dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a id="porApellidos" href="#"><i id="iconPorApellidos"></i> Ordenar por apellidos</a></li>
                </ul>
            </div>
        </div>
    </div>

    <h5><a style="float: right" href="#" target="_blank" id="printView" title="Versión imprimible del curso seleccionado"><i class="icon-print"></i> Versión imprimible</a></h5>
    <h5 class="text-info">Listado de graduados del curso <?php echo $cursos[$idCurso]; ?></h5>
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
            echo '<tr><td colspan="5" class="text-info">No hay estudiantes graduados</td></tr>';
        } else {
            $num = 0;
            foreach ($estudiantes as $id => $data) {
                $num++;

                echo '<tr>';
                echo "<td>{$num}</td>";
                if ($ordenarXApellidos)
                    echo "<td>{$data['apellido1']} {$data['apellido2']}, {$data['nombre']}</td>";
                else
                    echo "<td>{$data['nombre']} {$data['apellido1']} {$data['apellido2']}</td>";
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

<?php
//Lo que sigue no es necesario cuando es para imprimir
if ($imprimible)
    return;
?>

<script type="text/javascript">
    $(window).load(function() {
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

        //Cuando dé click en el botón actualizar actualizar la página con los datos del curso y las opciones
        $('#btnUpdate').click(function() {
            document.location = '<?php echo $url_pref; ?>index/' + $('#idCurso').val() + '/' + $('#inpPorApellidos').val();
        });

        //Cuando dé click en 'Versión imprimible'
        $('#printView').click(function(event) {
            $(this).attr({'href': '<?php echo $url_pref; ?>printable/' + $('#idCurso').val() + '/' + $('#inpPorApellidos').val()});
        });
    });
</script>
