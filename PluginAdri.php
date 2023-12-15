<?php
/**
 * Nombre del Plugin: Palabras Alternativas
 * URI del Plugin: http://wordpress.org/plugins/palabras-alternativas/
 * Descripción: Este plugin sustituye palabras inapropiadas en publicaciones por alternativas no ofensivas.
 * Autor: Adrian
 * Versión: 1.0.0
 * URI del Autor: http://ma.tt/
 */

// Gancho para crear la tabla y insertar datos al cargar el plugin
add_action("plugins_loaded", 'inicializarPlugin');

// Gancho para modificar el contenido de las publicaciones
add_filter('the_content', 'corregirTextoEnPublicaciones');

/**
 * Función para inicializar el plugin.
 * Crea la tabla en la base de datos y inserta los datos iniciales.
 */
function inicializarPlugin() {
    crearTablaPalabras();
    insertarPalabrasIniciales();
}

/**
 * Crear tabla en la base de datos para almacenar las palabras.
 */
function crearTablaPalabras() {
    global $wpdb;
    $nombreTabla = $wpdb->prefix . "palabrasAlternativas";
    $charsetCollate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $nombreTabla (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        palabraInapropiada varchar(255) NOT NULL,
        palabraAlternativa varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charsetCollate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Insertar palabras iniciales en la tabla.
 */
function insertarPalabrasIniciales() {
    global $wpdb;
    $nombreTabla = $wpdb->prefix . "palabrasAlternativas";
    $verificarDatos = $wpdb->get_results("SELECT * FROM $nombreTabla");

    if (count($verificarDatos) == 0) {
        $listaPalabrasInapropiadas = ["feo","tonto","atontao","guinomo"];
        $listaPalabrasAlternativas = ["guapo","listo","listillo","guapa"];

        foreach (array_combine($listaPalabrasInapropiadas, $listaPalabrasAlternativas) as $inapropiada => $alternativa) {
            $wpdb->insert($nombreTabla, ['palabraInapropiada' => $inapropiada, 'palabraAlternativa' => $alternativa]);
        }
    }
}

/**
 * Corregir texto en las publicaciones reemplazando palabras inapropiadas.
 * @param string $texto El texto original de la publicación.
 * @return string Texto modificado.
 */
function corregirTextoEnPublicaciones($texto) {
    $palabras = obtenerPalabrasDesdeBD();
    $buscar = array_column($palabras, 'palabraInapropiada');
    $reemplazar = array_column($palabras, 'palabraAlternativa');

    return str_replace($buscar, $reemplazar, $texto);
}

/**
 * Obtener palabras desde la base de datos.
 * @return array Lista de palabras inapropiadas y sus alternativas.
 */
function obtenerPalabrasDesdeBD() {
    global $wpdb;
    $nombreTabla = $wpdb->prefix . 'palabrasAlternativas';
    return $wpdb->get_results("SELECT palabraInapropiada, palabraAlternativa FROM $nombreTabla");
}
