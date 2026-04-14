<?php
/**
 * Sistema de internacionalización (i18n) - Mizton Landing
 *
 * Detecta el idioma desde la URL (/es → es, /us → en),
 * cookie de preferencia o default español.
 *
 * Uso: <?= __('lp.hero_title_main') ?>
 */

$_LP_TRANSLATIONS = null;
$_LP_CURRENT_LANG = null;

/**
 * Obtiene el idioma activo.
 * Prioridad: GET param (puesto por .htaccess) > cookie > default 'es'
 */
function getCurrentLang() {
    global $_LP_CURRENT_LANG;

    if ($_LP_CURRENT_LANG !== null) {
        return $_LP_CURRENT_LANG;
    }

    $supported = ['es', 'en'];

    // 1. GET param establecido por .htaccess (?lang=es o ?lang=en)
    if (isset($_GET['lang']) && in_array($_GET['lang'], $supported)) {
        $lang = $_GET['lang'];
        // Guardar preferencia en cookie 30 días
        if (!headers_sent()) {
            setcookie('mizton_landing_lang', $lang, time() + 86400 * 30, '/', '.mizton.cat', false, false);
        }

    // 2. Cookie de preferencia
    } elseif (isset($_COOKIE['mizton_landing_lang']) && in_array($_COOKIE['mizton_landing_lang'], $supported)) {
        $lang = $_COOKIE['mizton_landing_lang'];

    // 3. Default español
    } else {
        $lang = 'es';
    }

    $_LP_CURRENT_LANG = $lang;
    return $lang;
}

/**
 * Carga el archivo de traducciones del idioma activo.
 */
function loadTranslations() {
    global $_LP_TRANSLATIONS;

    if ($_LP_TRANSLATIONS !== null) {
        return $_LP_TRANSLATIONS;
    }

    $lang = getCurrentLang();
    $file = __DIR__ . "/{$lang}.php";

    if (file_exists($file)) {
        $_LP_TRANSLATIONS = require $file;
    } else {
        $_LP_TRANSLATIONS = require __DIR__ . '/es.php';
    }

    return $_LP_TRANSLATIONS;
}

/**
 * Traduce una clave al idioma activo.
 *
 * @param string $key          Clave de traducción (ej: 'lp.nav_join')
 * @param array  $replacements Reemplazos dinámicos (ej: ['name' => 'Juan'])
 * @return string
 */
function __($key, $replacements = []) {
    $translations = loadTranslations();
    $text = $translations[$key] ?? $key;

    foreach ($replacements as $k => $v) {
        $text = str_replace(":{$k}", $v, $text);
    }

    return $text;
}

/**
 * Genera la URL para cambiar de idioma conservando el código de referido.
 *
 * @param string $lang  'es' o 'en'
 * @return string  URL relativa: /es, /us o /es?ref=XXX, /us?ref=XXX
 */
function getLangUrl($lang) {
    $params = ['lang' => $lang];
    if (isset($_SESSION['referido']) && !empty($_SESSION['referido'])) {
        $params['ref'] = $_SESSION['referido'];
    }
    return '?' . http_build_query($params);
}
