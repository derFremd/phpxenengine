<?php

require_once __DIR__ . '/../classes/init.php';

use PHPXenEngine\Template\TemplateFileLoader as TemplateFileLoader;

$path = realpath(__DIR__ . '/../tpl/samples');

// Loading a template without binding by language.
$testClass1 = new TemplateFileLoader("SimpleTemplate", $path);

// Loading a template with language binding (en)
$testClass2 = new TemplateFileLoader("SimpleTemplate", $path,'en');

// Loading the default template (only by name) when language template is absent.
$testClass3 = new TemplateFileLoader("SimpleTemplate", $path, 'en-US');

// Error message when trying to load absent template.
$testClass4 = new TemplateFileLoader('Template-not-found-test', $path);

echo '<i>TemplateLoader(' . $testClass1->getName() . ')->get()=</i>' . $testClass1->get();
echo '<i>TemplateLoader(' . $testClass2->getName() . ')->get()=</i>' . $testClass2->get();
echo '<i>TemplateLoader(' . $testClass3->getName() . ')->get()=</i>' . $testClass3->get();
echo '<i>TemplateLoader(' . $testClass4->getName() . ')->get()=</i>' . $testClass4->get();
