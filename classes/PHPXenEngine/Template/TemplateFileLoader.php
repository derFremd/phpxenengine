<?php

namespace PHPXenEngine\Template;

use PHPXenEngine\Template\TemplateLoader as TemplateLoader;

/**
 * TemplateFileLoader.php
 *
 * This class for loading template from a file.
 * See class {@link TemplateLoader}.
 *
 * @package PHPXenEngine
 * @author Sergey S. <def.fremd@gmail.com>
 * @version v 0.1 (2022)
 */
class TemplateFileLoader extends TemplateLoader
{

    /**
     * Enabled file extensions
     */
    public const EXT_FILES = ['html', 'tpl', 'css', 'txt'];

    /*
     * The path where the template is
     */
    private string $path;

    /**
     * Constructor.
     * @param string $tplName Template file name (without extension)
     * @param string $path Path to the template folder
     * @param string $lang string with default language suffix
     */
    function __construct(string $tplName, string $path = __DIR__, string $lang = parent::DEFAULT_LANG)
    {
        parent::__construct($tplName, $lang);
        $this->path = $path;
    }

    /**
     * Specifies the path where the template will be searched for.
     * @param string $path template path
     * @return $this reference to this instance of the class
     */
    public function setPath(string $path): self
    {
        $this->path = $path;
        $this->reset();
        return $this;
    }

    /**
     * Returns the path where the template will be searched for.
     * @return string template path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Function returns array of enabled template extensions.
     * @return array extensions array
     */
    public static function getExtensions(): array
    {
        return self::EXT_FILES;
    }

    /**
     * Function returns full filename of the template with enabled extension.
     * Language-bound templates have a priority.
     * @return string full filename of the template or empty string if it's not found.
     */
    protected function searchFullName(): string
    {
        $names = [0 => $this->getName()];
        if ($this->isLang()) $names[1] = $this->getName(true);

        foreach ($names as $name) {
            // fill path to template file
            $fullName = empty($this->path) ? $name : $this->path . DIRECTORY_SEPARATOR . $name;
            foreach (self::EXT_FILES as $ext) {
                if (is_readable($fullNameExt = ($fullName . '.' . $ext))) {
                    return $fullNameExt;
                }
            }
        }
        return '';
    }

    /**
     * Function returns body of the template.
     * @return string template as string
     */
    protected function load(): string
    {
        return !empty($fullName = $this->searchFullName()) ? file_get_contents($fullName) :
            'Error: template \'' . $this->getName() . '\' is not found at \'' . $this->path . '\'';
    }
} // End of class TemplateFileLoader