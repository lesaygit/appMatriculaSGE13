<?php extract($datos); ?>
<h4>Datos del estudiante</h4>
<table class="table-condensed">
    <tr><td style="text-align: right; width: 150px">Nombre(s) y apellidos</td><td><?php echo $nombre . ' ' . $apellido1 . ' ' . $apellido2; ?></td></tr>
    <tr><td style="text-align: right">Carnet de identidad</td><td><?php echo $CI; ?></td></tr>
    <tr><td style="text-align: right">Sexo</td><td><?php echo $sexo == 'M' ? 'Masculino' : 'Femenino'; ?></td></tr>
    <tr><td style="text-align: right">Dirección</td><td><?php echo $direccion ? $direccion : '-'; ?></td></tr>
    <tr><td style="text-align: right">Localidad</td><td><?php echo $localidad; ?></td></tr>
    <tr><td style="text-align: right">Talla</td><td><?php echo $talla ? $talla : '-'; ?></td></tr>
    <tr><td style="text-align: right">Peso</td><td><?php echo $peso ? $peso : '-'; ?></td></tr>
    <tr><td style="text-align: right">Color de piel</td><td><?php echo $color_piel ? $color_piel : '-'; ?></td></tr>
    <tr><td style="text-align: right">Color de ojos</td><td><?php echo $color_ojos ? $color_ojos : '-'; ?></td></tr>
    <tr><td style="text-align: right">Color de pelo</td><td><?php echo $color_pelo ? $color_pelo : '-'; ?></td></tr>
</table>

<h4>Historial</h4>

<table class="table table-condensed">
    <tr><th align="left" width="100">Curso</th><th align="left" width="100">Grupo</th><th align="left">Estado</th></tr>
    <?php foreach ($cursos as $datosCurso) { ?>
        <tr>
            <td><?php echo $datosCurso['curso']; ?></td>
            <td><?php echo $datosCurso['grupo']; ?></td>
            <td><?php
                echo $datosCurso['estado'];
                if ($datosCurso['idestado'] == 1)
                    echo '. ' . htmlentities($datosCurso['obs_baja']);
                ?></td>
        </tr>
    <?php } ?>
</table>