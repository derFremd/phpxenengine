<?php

require_once __DIR__ . '/../classes/init.php';

use PHPXenEngine\Template\TemplateLoader as TemplateLoader;

class TemplateLoaderImpl extends TemplateLoader {

    function __construct(string $tplName = self::DEFAULT_NAME, string $lang = self::DEFAULT_LANG)
    {
        parent::__construct($tplName, $lang);
    }

    protected function load(): string {
        return 'string of \'' . $this->getName() . '\' template';
    }
}

// Template with default setup.
$testClass1 = new TemplateLoaderImpl();

// Template with name setup.
$testClass2 = new TemplateLoaderImpl();
$testClass2->setName("TestName1");

// Template with name and language setup.
$testClass3 = new TemplateLoaderImpl();
$testClass3->setName("TestName2","en");

echo '<p>TemplateLoader(' . $testClass1->getName() . ')->get()=' . $testClass1->get();
echo '<p>TemplateLoader(' . $testClass2->getName() . ')->get()=' . $testClass2->get();
echo '<p>TemplateLoader(' . $testClass3->getName() . ')->get()=' . $testClass3->get();

