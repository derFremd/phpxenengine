<?php

namespace Tests;

//require_once __DIR__ . '/../classes/PHPXenEngine/Template/TemplateLoader.php';
//require_once __DIR__ . '/../classes/PHPXenEngine/Template/TemplateFileLoader.php';

use PHPUnit\Framework\TestCase as TestCase;
use PHPXenEngine\Template\TemplateFileLoader as TemplateFileLoader;

class TemplateFileLoaderTest extends TestCase
{

    public function testGetPath()
    {
        $tl = new TemplateFileLoader('name');
        $path = $tl->getPath();
        $this->assertNotEmpty(!empty($path));
    }

    public function test__construct()
    {
        $tl = new TemplateFileLoader('name');
        $this->assertInstanceOf(TemplateFileLoader::class, $tl);
    }

    public function testGetExtensions()
    {
        $ext = TemplateFileLoader::getExtensions();
        $this->assertIsArray($ext);
        $this->assertNotEmpty($ext);
    }

    public function testSetPath()
    {
        $path = realpath(__DIR__);
        $tl = new TemplateFileLoader('name', $path);
        $this->assertEquals($path, $tl->getPath());
    }

    public function testNameDirSeparatorProtect() {
        $arrayOfName = ['Checks','Directory','Separator','Protect'];
        $arrayOfExt = ['n','o'];

        $nameWithSep = implode(DIRECTORY_SEPARATOR, $arrayOfName);
        $extWithSep = implode(DIRECTORY_SEPARATOR, $arrayOfExt);

        $nameNoSep = implode($arrayOfName) . '.' . implode($arrayOfExt);

        $tl = new TemplateFileLoader($nameWithSep, __DIR__, $extWithSep);
        $this->assertEquals($nameNoSep, $tl->getName());

    }

    public function testFileNotFound() {
        $tl = new TemplateFileLoader(uniqid());
        $message = $tl->get();
        $this->assertStringStartsWith('Error', $message, $message);
    }

    public function testFileFound() {
        $name = 'IncludedFile';
        $ext = '.html';
        $path = realpath(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'tpl', 'samples'])) . DIRECTORY_SEPARATOR;
        $tl = new TemplateFileLoader($name, $path);

        $content = file_get_contents($path . $name . $ext);

        $this->assertEquals($content, $tl->get());
    }
}
