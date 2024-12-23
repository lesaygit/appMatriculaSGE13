</div>

</form>
<br/><br/><br/><br/><br/>
</div>
</div>

<div class="row">
    <div class="span12">
        <div class="navbar navbar-inverse navbar-fixed-bottom">
            <div class="navbar-inner">
                <div class="container">
                    <span class="brand">Control de Matrícula</span>
                    <ul class="nav pull-right">
                        <li><a href="javascript:void"><?php echo get_instance()->session_values->datosConfig['centro']; ?></a></li>
                        <li class="divider-vertical"></li>
                        <li class="brand"><?php echo get_instance()->curso->activo_str(); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script type="text/javascript">
    setTimeout(function() {
        $('#successDiv').fadeOut('slow');
    }, 2000);
</script>
</body>
</html>