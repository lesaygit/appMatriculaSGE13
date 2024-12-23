<div class="row-fluid">
    <div class="span3">
        <b>Contraseña actual</b><br />
        <input type="password" name="oldpw" placeholder="Contraseña actual" value="" size="50" class="input-medium" />    
    </div>

    <div class="span4">
        <b>Nueva contraseña</b><br />
        <input type="password" name="newpw" placeholder="Nueva contraseña" value="" size="50" class="input-medium" />
        <br />
        <b>Confirmar contraseña</b><br />
        <input type="password" name="confirm" placeholder="Confirmar contraseña" value="" size="50" class="input-medium" />
    </div>
</div>

<div class="row-fluid">
    <div class="span7">
        <hr/>
        <div style="float: right">
            <input type="submit" class="btn btn-primary" value="Actualizar" />
        </div>
    </div>
</div>

<br/>

<?php if ($error || $error = validation_errors('<div class="">', '</div>')): ?>
    <div class="row-fluid">
        <div class="span7">
            <div class="alert alert-error">
                <button type="button" class="close" data-dismiss="alert">x</button>
                <h4 class="">Error</h4>
                <?php echo $error; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
    