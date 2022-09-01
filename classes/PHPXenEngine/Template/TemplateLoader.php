<?php

namespace PHPXenEngine\Template;

/**
 * TemplateLoader.php
 *
 * This is an abstract class to realise loading template from anywhere by implement
 * abstract function {@link TemplateLoader::load()}.
 *
 * @package PHPXenEngine
 * @author Sergey S. <def.fremd@gmail.com>
 * @version v 0.1 (2022)
 */
abstract class TemplateLoader
{

    /**
     * Default template name
     */
    public const DEFAULT_NAME = 'default';

    /**
     * Default template lang
     */
    public const DEFAULT_LANG = '';

    /*
     * Template name
     */
    private string $tplName;

    /*
     * Template body
     */
    private string $tplBody;

    /*
     * Template language
     */
    private string $lang;

    /*
     * Template loaded flag
     */
    private bool $isLoaded;

    /**
     * Default constructor of TemplateLoader class.
     * @param string $tplName template name
     * @param string $lang language suffix (By default, an empty string is used)
     */
    public function __construct(string $tplName = self::DEFAULT_NAME, string $lang = self::DEFAULT_LANG)
    {
        $this->setName($tplName, $lang);
    }

    /**
     * Function sets name of template.
     * @param string $tplName template name
     * @param string $lang template language
     */
    public function setName(string $tplName, string $lang = self::DEFAULT_LANG)
    {
        $this->tplName = $tplName;
        $this->lang = $lang;
        $this->reset();
    }

    /**
     * Function reset loaded template.
     * @return void
     */
    public function reset()
    {
        $this->isLoaded = false;
        $this->tplBody = '';
    }

    /**
     * Function returns template name
     * @param bool $noLang is true when name without language suffix
     * @return string template name
     */
    public function getName(bool $noLang = false): string
    {
        return empty($this->lang) || $noLang ? $this->tplName : $this->tplName . '.' . $this->lang;
    }

    /**
     * Function returns template language.
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * Function returns language status.
     * @return bool returns true if language is set
     */
    public function isLang(): bool
    {
        return !empty($this->lang);
    }

    /**
     * Returns template body as a string. When template still is not loaded, then tries to load it.
     * @return string template body string.
     */
    public function get(): string
    {
        return $this->isLoaded ? $this->tplBody : $this->set($this->load());
    }

    /**
     * Function returns template body string.
     * This is a magic function that is automatically called when an object is converted to a string.
     * @return string
     */
    public function __toString(): string
    {
        return $this->get();
    }

    /**
     * Function to realise template loading procedure.
     * To implement your loading function, redefine it in your class.
     * @return string loaded template string
     */
    abstract protected function load(): string;

    /**
     * Function sets template body string.
     * @param string $tplBody template body string
     * @return void
     */
    private function set(string $tplBody): string
    {
        $this->isLoaded = true;
        $this->tplBody = $tplBody;
        return $this->tplBody;
    }
} // End of class TemplateLoader