<?php

namespace PHPXenEngine\Template;

require_once __DIR__ . '/../classes/PHPXenEngine/Template/TemplateLoader.php';

use PHPUnit\Framework\TestCase as TestCase;
use PHPXenEngine\Template\TemplateLoader as TemplateLoader;

class TLI extends TemplateLoader {

    public const DEFAULT_BODY = 'template body';

    private bool $random = false;

    function __construct(string $tplName = self::DEFAULT_NAME, string $lang = self::DEFAULT_LANG)
    {
        parent::__construct($tplName, $lang);
    }
    public function random(): self {
        $this->random = true;
        return $this;
    }

    protected function load(): string {
        return $this->random ? uniqid() : self::DEFAULT_BODY;
    }
}

class TemplateLoaderTest extends TestCase
{
    public function test__construct() {
        $tl = new TLI();
        $this->assertInstanceOf(TemplateLoader::class, $tl);
    }

    public function testGetNameNoLang()
    {
        $tl = new TLI('TemplateName');
        $this->assertEquals('TemplateName', $tl->getName());
    }

    public function testGetNameLang()
    {
        $tl = new TLI('TemplateName','en');
        $this->assertEquals('TemplateName.en', $tl->getName());
    }

    public function testSetName()
    {
        ($tl = new TLI())->setName($name = uniqid());
        $this->assertEquals($name, $tl->getName());
    }

    public function testGetDefaultLang()
    {
        $tl = new TLI('name');
        $this->assertEmpty($tl->getLang());
    }

    public function testGetLang()
    {
        $tl = new TLI('name','en');
        $this->assertEquals('en', $tl->getLang());
    }

    public function testIsLangDefault() {
        $tl = new TLI('name');
        $this->assertFalse($tl->isLang());
    }

    public function testIsLang() {
        ($tl = new TLI('name', 'fr'));
        $this->assertTrue( $tl->isLang());
    }

    public function testGet() {
        $tl = new TLI();
        $this->assertEquals(TLI::DEFAULT_BODY, $tl->get());
    }

    public function testReset() {
        $tl = new TLI();
        $this->assertNotEquals($tl->get(), $tl->random()->reset()->get());
    }

    public function testGetAnd__toStringTheSame() {
        ($tl = new TLI())->random()->reset();
        $this->assertEquals($tl->get(), $tl);
    }
}
