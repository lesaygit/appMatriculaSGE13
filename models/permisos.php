<?php

define('PERM_INFORMES', 1);
define('PERM_GESTIONAR_ESTUDIANTES', 2);
define('PERM_PROCESAR_ESTUDIANTES', 3);
define('PERM_DAR_BAJA', 4);
define('PERM_GESTIONAR_GRUPOS', 5);
define('PERM_AVANZAR_CURSO', 6);

class Permisos extends CI_Model {
    /*
     * Define los controladores (llave) y sus permisos (valor). Los
     * controladores que no se incluyan serán visibles por cualquier usuario.
     * Un valor de '*' indica que solo se aplica a usuarios con el rol de
     * administrador
     */

    public $permiso = array(
        'localidades' => '*',
        'usuarios' => '*',
        'roles' => '*',
        'color_ojos' => '*',
        'color_piel' => '*',
        'color_pelo' => '*',
        'estudiantes' => PERM_GESTIONAR_ESTUDIANTES,
        'grupos' => PERM_GESTIONAR_GRUPOS,
        'grupos_cs' => PERM_GESTIONAR_GRUPOS,
        'procesar' => PERM_PROCESAR_ESTUDIANTES,
        'init_curso' => PERM_AVANZAR_CURSO,
        'activar_curso' => PERM_AVANZAR_CURSO,
        'listado_grupo' => PERM_INFORMES,
        'listado_curso' => PERM_INFORMES,
        'graduados' => PERM_INFORMES,
        'detalle' => PERM_INFORMES,
        'buscar' => PERM_INFORMES,
        'resumenes' => PERM_INFORMES,
        'resumen_matricula' => PERM_INFORMES,
        'resumen_color' => PERM_INFORMES,
    );

    /*
     * Determina si el usuario activo tiene permiso para ejecutar el controlador especificado.
     * Se utiliza solo después de que el usuario se autentifique, de esta forma
     * los datos de permisos están en la sesión
     * Devuelve:
     *  TRUE Si tiene el permiso
     *  FALSE Si no tiene el permiso
     */

    public function puede_ejecutar($controlador) {
        if (!isset($this->permiso[$controlador]))
            return true;

        $permiso = $this->permiso[$controlador];

        if ($permiso == '*')
            return $this->session->userdata('isAdmin');

        return in_array($permiso, $this->session_values->permisos);
    }

    /*
     * Obtiene la lista de todos los permisos
     * Devuelve:
     *  NULL: Si no es posible obtener los datos
     *  Array: Contiene los registros obtenidos. La llave es el ID y el valor es el nombre.
     */

    public function lista() {
        $query = $this->db->query('SELECT idpermiso, nombre FROM permisos');

        if ($query === FALSE)
            return null;

        $result = array();

        foreach ($query->result() as $row)
            $result[$row->idpermiso] = $row->nombre;

        return $result;
    }
}

?>