<?php
$url_pref = base_url() . 'index.php/resumen_matricula/';
extract($datos);
?>
<input autocomplete="off" type="hidden" id="curCurso" value="<?php echo $idCurso; ?>" />

<?php if (!$imprimible): ?>
    <div class="row-fluid">
        <div class="span3">
            <strong>Curso</strong><br/>
            <?php echo form_dropdown('idCurso', $cursos, $idCurso, 'id="idCurso" autocomplete="off" class="input-medium"'); ?>
        </div>

        <div class="span9">
            <h5><a style="margin-top: 15px; float: right" href="#" target="_blank" id="printView"><i class="icon-print"></i> Versión imprimible</a></h5>
        </div>
    </div>
<?php
endif;

if (empty($resumen)) {
    echo '<br/><p class="text-info">No hay datos que mostrar</p>';
} else {
    $grados = array('7' => 'Séptimo', '8' => 'Octavo', '9' => 'Noveno');

    $totalCurso = array('M' => 0, 'F' => 0);

    echo '<div class="row-fluid">';
    foreach ($resumen as $grado => $grupos) {
        ?>
        <div class="span4">
            <div class="thumbnail">
                <div class="caption">
                    <h4 class="page-header" style="margin: 0px"><?php echo $grados[$grado]; ?></h4>

                    <table class="table table-condensed">
                        <tr>
                            <th style="text-align: center">Grupo</th>
                            <th style="text-align: center">Masc.</th>
                            <th style="text-align: center">Fem.</th>
                            <th style="text-align: center">Total</th>
                        </tr>
                        <?php
                        $totalGrado = array('M' => 0, 'F' => 0);

                        foreach ($grupos as $nombre => $sexos) {
                            $M = empty($sexos['M']) ? 0 : $sexos['M'];
                            $F = empty($sexos['F']) ? 0 : $sexos['F'];

                            $totalGrado['M'] += $M;
                            $totalGrado['F'] += $F;
                            $totalCurso['M'] += $M;
                            $totalCurso['F'] += $F;
                            ?>
                            <tr>
                                <td style="text-align: center"><?php echo $grado . '-' . $nombre; ?></td>
                                <td style="text-align: center"><?php echo $M; ?></td>
                                <td style="text-align: center"><?php echo $F; ?></td>
                                <td style="text-align: center"><?php echo $M + $F; ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <td style="text-align: center"><strong>Total</strong></td>
                            <td style="text-align: center"><strong><?php echo $totalGrado['M']; ?></strong></td>
                            <td style="text-align: center"><strong><?php echo $totalGrado['F']; ?></strong></td>
                            <td style="text-align: center"><strong><?php echo $totalGrado['M'] + $totalGrado['F']; ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    echo '</div>';
    ?>
    <br/>
    <div class="row-fluid">
        <div class="span4">
            <strong>Totales del curso</strong>
            <br/>
            <table class="table table-condensed">
                <tr>
                    <th>Sexo</th>
                    <th>Total</th>
                </tr>
                <td>Masculino</td>
                <td><?php echo $totalCurso['M']; ?></td>
                </tr>
                <tr>
                    <td>Femenino</td>
                    <td><?php echo $totalCurso['F']; ?></td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong><?php echo $totalCurso['M'] + $totalCurso['F']; ?></strong></td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

if (!$imprimible):
    ?>
    <script type="text/javascript">
        $(window).load(function() {
            $('#idCurso').change(function() {
                document.location = '<?php echo $url_pref; ?>index/' + $('#idCurso').val();
            });

            //Cuando dé click en 'Versión imprimible'
            $('#printView').click(function() {
                $(this).attr({'href': '<?php echo $url_pref; ?>printable/' + $('#idCurso').val()});
            });
        });
    </script>
<?php endif; ?>