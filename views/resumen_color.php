<?php
$url_pref = base_url() . 'index.php/resumen_color/';
extract($datos);

if (!$imprimible):
    ?>
    <input autocomplete="off" type="hidden" id="curCurso" value="<?php echo $idCurso; ?>" />

    <div class="row-fluid">
        <div class="span3">
            <strong>Curso</strong><br/>
            <?php echo form_dropdown('idCurso', $cursos, $idCurso, 'id="idCurso" autocomplete="off" class="input-medium"'); ?>
        </div>

        <div class="span6">
            <br/>
            <div class="btn-group">
                <a href="#" id="btn_piel" class="btn<?php echo $colorDe == 'piel' ? ' active' : ''; ?>">Piel</a>
                <a href="#" id="btn_pelo" class="btn<?php echo $colorDe == 'pelo' ? ' active' : ''; ?>">Pelo</a>
                <a href="#" id="btn_ojos" class="btn<?php echo $colorDe == 'ojos' ? ' active' : ''; ?>">Ojos</a>
            </div>
        </div>

        <div class="span3">
            <h5><a style="margin-top: 15px; float: right" href="#" target="_blank" id="printView"><i class="icon-print"></i> Versión imprimible</a></h5>
        </div>
    </div>
    <?php
endif;

if (empty($resumen)) {
    echo 'No hay datos que mostrar';
} else {
    $grados = array('7' => 'Séptimo', '8' => 'Octavo', '9' => 'Noveno');

    $r = 0;
    foreach ($resumen as $color => $data) {
        if (($r % 4) == 0) {
            if ($r)
                echo '</div><br/>';
            echo '<div class="row-fluid">';
        }
        $r++;

        $totalGrados = array(7 => 0, 8 => 0, 9 => 0);
        $totalColor = 0;

        echo '<div class="span3">';
        echo '<div class="thumbnail">';
        echo '<div class="caption">';
        echo "<h4 class=\"page-header\" style=\"margin: 0px\">{$color}</h4>";

        echo '<table class="table-condensed"><tr><th>Grupo</th><th>Cantidad</th></tr>';
        foreach ($data as $grado => $grupos) {
            foreach ($grupos as $grupo => $cant) {
                echo "<tr><td align=\"right\">{$grado}-{$grupo}</td><td align=\"center\">{$cant}</td></tr>";
                $totalGrados[$grado] += $cant;
                $totalColor += $cant;
            }
        }
        echo '</table>';
        echo '<br/>';
        echo '<table class="table-condensed"><tr><th>Grado</th><th>Cantidad</th></tr>';
        foreach ($totalGrados as $grado => $cant)
            echo "<tr><td align=\"right\">{$grados[$grado]}</td><td align=\"center\">{$cant}</td></tr>";
        echo '</table>';
        echo '<br/><h4 align="right"><strong>Total</strong>: ' . $totalColor . '</h4>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
}

if (!$imprimible):
    ?>
    <script type="text/javascript">
        $(window).load(function() {
            $('#idCurso').change(function() {
                document.location = '<?php echo $url_pref; ?>index/' + $('#idCurso').val() + '/<?php echo $colorDe; ?>';
            });

            //Cuando dé click en el botón actualizar actualizar la página con los datos del grupo/grado y las opciones
            $('a.btn').click(function(event) {
                event.preventDefault();
                document.location = '<?php echo $url_pref; ?>index/' + $('#idCurso').val() + '/' + $(this).attr('id').substr(4);
            });

            //Cuando dé click en 'Versión imprimible'
            $('#printView').click(function() {
                $(this).attr({'href': '<?php echo $url_pref; ?>printable/' + $('#idCurso').val() + '/<?php echo $colorDe; ?>'});
            });
        });
    </script>
<?php endif; ?>