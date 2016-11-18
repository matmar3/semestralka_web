<?php

/**
 * MINI - an extremely simple naked PHP application
 *
 * @package mini
 * @author Panique
 * @link http://www.php-mini.com
 * @link https://github.com/panique/mini/
 * @license http://opensource.org/licenses/MIT MIT License
 */

/**
 * Now MINI work with namespaces + composer's autoloader (PSR-4)
 *
 * @author Joao Vitor Dias <joaodias@noctus.org>
 *
 * For more info about namespaces plase @see http://php.net/manual/en/language.namespaces.importing.php
 */
// cesta do korenove slozky
define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
// cesta do "/www/application".
define('APP', ROOT . 'application' . DIRECTORY_SEPARATOR);

// This is the auto-loader for Composer-dependencies (to load tools into your project).
require ROOT . 'vendor/autoload.php';

// load application config (error reporting etc.)
require APP . 'config/config.php';

// load application class
use Mini\Core\Application;

// start the application
$app = new Application();
