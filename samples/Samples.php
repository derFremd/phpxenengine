<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<?php

require_once __DIR__ . '/../classes/init.php';

$tests = [
    'ClassLoaderSample'=>'ClassLoaderSample.php',
    'TemplateLoaderSample'=>'TemplateLoaderSample.php',
    'TemplateFileLoaderSample'=>'TemplateFileLoaderSample.php',
    'VisualBlockSample'=>'VisualBlockSample.php',
];

$test_id = $_GET['id'];

if(!isset($test_id) || !isset($tests[$test_id])) {
    echo "Test '$test_id' is not found"; die();
}

echo "<h3>Sample name: $tests[$test_id]</h3>";

echo "Source<hr><pre><code>" . htmlspecialchars(file_get_contents(__DIR__ . '/' .$tests[$test_id])) .
    "\n// End of source " . $tests[$test_id] ."</code></pre>\n";

echo '<hr>Begin of sample result<hr>';
include_once __DIR__ . '/' . $tests[$test_id];
echo '<hr>End of sample result<hr>';