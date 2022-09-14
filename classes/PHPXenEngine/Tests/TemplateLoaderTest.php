<?php

namespace PHPXenEngine\Tests;

//require_once __DIR__ . '/../classes/PHPXenEngine/Template/TemplateLoader.php';

use PHPUnit\Framework\TestCase as TestCase;
use PHPXenEngine\Tests\TemplateLoaderImplPHPUnit as TLImpl;

class TemplateLoaderTest extends TestCase
{
    public function test__construct()
    {
        $this->assertInstanceOf(TLImpl::class, new TLImpl());
        $this->assertInstanceOf(TLImpl::class, new TLImpl('theName'));
        $this->assertInstanceOf(TLImpl::class, new TLImpl('theName',''));
        $this->assertInstanceOf(TLImpl::class, new TLImpl('theName','en'));
    }

    public function testGetNameNoLang()
    {
        $tl = new TLImpl('TemplateName');
        $this->assertEquals('TemplateName', $tl->getName());
    }

    public function testGetNameLang()
    {
        $tl = new TLImpl('TemplateName','en');
        $this->assertEquals('TemplateName.en', $tl->getName());
    }

    public function testSetName()
    {
        ($tl = new TLImpl())->setName($name = uniqid());
        $this->assertEquals($name, $tl->getName());
    }

    public function testGetDefaultLang()
    {
        $tl = new TLImpl('name');
        $this->assertEmpty($tl->getLang());
    }

    public function testGetLang()
    {
        $tl = new TLImpl('name','en');
        $this->assertEquals('en', $tl->getLang());
    }

    public function testIsLangDefault() {
        $tl = new TLImpl('name');
        $this->assertFalse($tl->isLang());
    }

    public function testIsLang() {
        ($tl = new TLImpl('name', 'fr'));
        $this->assertTrue( $tl->isLang());
    }

    public function testGet() {
        $tl = new TLImpl();
        $this->assertEquals(TLImpl::DEFAULT_BODY, $tl->get());
    }

    public function testReset() {
        $tl = new TLImpl();
        $this->assertNotEquals($tl->get(), $tl->random()->reset()->get());
    }

    public function testGetAnd__toStringTheSame() {
        ($tl = new TLImpl())->random()->reset();
        $this->assertEquals($tl->get(), $tl);
    }
}
