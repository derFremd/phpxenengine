<?php

/*
 * Class loader initialisation script.
 */

// Deny trying load directly.
(getcwd() == dirname(__FILE__)) && die('Directly start. Access deny.');

// By default, all classes are relative to this file.
defined('CLASS_LOADER_PATH') || define('CLASS_LOADER_PATH', __DIR__);

require_once __DIR__ . '/PHPXenEngine/Utils/ClassLoader.php';

PHPXenEngine\Utils\ClassLoader::init();