<?php
$url_pref = base_url() . 'index.php/listado_curso/';
?>
<input autocomplete="off" type="hidden" id="curCurso" value="<?php echo $idCurso; ?>" />

<?php if (!$imprimible): ?>
    <div class="row-fluid">
        <div class="span3">
            <strong>Curso</strong><br/>
            <?php echo form_dropdown('idCurso', $cursos, $idCurso, 'id="idCurso" autocomplete="off" class="input-medium"'); ?>
        </div>

        <div class="span2">
            <br/>
            <button type="button" id="btnUpdate" class="btn">Actualizar</button>
        </div>

    </div>

    <h5><a style="float:right" href="<?php echo $url_pref . "printable/{$idCurso}/{$page}"; ?>" target="_blank" id="printView"><i class="icon-print"></i> Versión imprimible</a></h5>
    <h5><a style="float:right" href="<?php echo $url_pref . "pdf/{$idCurso}/{$page}"; ?>" target="_blank" id="printView" title="Guardar como PDF"><img src="<?php echo base_url() . '/resources/images/pdf.png' ?>" width="16" height="15" /> Descargar&nbsp;&nbsp;&nbsp;</a></h5>
    <h5 class="text-info">Listado de estudiantes del curso <?php echo $cursos[$idCurso]; ?></h5>
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
            echo '<tr><td colspan="5" class="text-info">No existen estudiantes en este curso</td></tr>';
        } else {
            $num = 0;
            $o = 0;
            $c = 0;
            foreach ($estudiantes as $id => $data) {
                $num++;

                if ($o++ < $offset)
                    continue;
                if ($c++ == $perPage)
                    break;

                $baja = $data['estado'] == 1;

                echo '<tr' . ($baja ? ' class="muted" style="text-decoration: line-through" title="Motivo de la baja: ' . htmlentities($data['obs_baja']) . '"' : '') . '>';
                echo "<td>{$num}</td>";
                echo "<td>{$data['nombre']} {$data['apellido1']} {$data['apellido2']}</td>";
                echo "<td>{$data['CI']}</td>";
                echo '<td>' . ($data['sexo'] == 'M' ? 'Masculino' : 'Femenino') . '</td>';
                if (!$imprimible)
                    echo '<td><a href="' . base_url() . "index.php/detalle/index/{$id}\" target=\"_blank\" class=\"icon-tasks\" title=\"Ver detalles\"></a></td>";
                echo '</tr>';
            }
        }
        ?>
    </tbody>
</table>

<?php
if (!$imprimible)
    echo get_instance()->pagination->create_links();
?>

<script type="text/javascript">
    $(window).load(function() {
        //Cuando dé click en el botón actualizar actualizar la página con los datos del grupo/grado y las opciones
        $('#btnUpdate').click(function() {
            document.location = '<?php echo $url_pref; ?>index/' + $('#idCurso').val() + '/1';
        });

        //Cuando dé click en 'Versión imprimible'
        $('#printView').click(function(event) {
            $(this).attr({'href': '<?php echo $url_pref; ?>printable/' + $('#idCurso').val() + '/1'});
        });
    });
</script>
