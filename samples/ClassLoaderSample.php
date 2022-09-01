<?php

require_once __DIR__ . '/../classes/init.php';

use PHPXenEngine\Test\Test as Test;

$testClass = new Test();

echo "<h4>Dump of loaded class Test</h4>";
print "<pre>";
var_dump($testClass);
print "</pre>";
