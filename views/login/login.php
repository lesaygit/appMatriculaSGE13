<div class="row">
    <div class="offset2 span3">
        <h3>Iniciar sesión</h3>

        <b>Usuario</b><br />
        <input class="span3" type="text" name="username" placeholder="Escriba su nombre de usuario" value="<?php echo set_value('username') ?>" size="50" >
        <br />
        <b>Contraseña</b><br />
        <input class="span3" type="password" name="password" placeholder="Escriba su contraseña" value="" size="50" />
    </div>
    <div class="span3">
        <br/><br/><br/><br/>
        <input type="submit" class="btn-large btn-primary" value="Iniciar" />
    </div>
</div>

<?php if ($error): ?>
    <div class="row">
        <div class="offset2 span5">
            <div class="alert alert-error">
                <button type="button" class="close" data-dismiss="alert">x</button>
                <h4>Error</h4>
                <?php echo $error; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
