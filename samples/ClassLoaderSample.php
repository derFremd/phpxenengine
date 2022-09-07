<?php

require_once __DIR__ . '/../classes/init.php';

use PHPXenEngine\Tests\EmptyClass as EmptyClass;

$testClass = new EmptyClass();

echo "<h4>Dump of loaded class Test</h4>";
print "<pre>";
var_dump($testClass);
print "</pre>";
