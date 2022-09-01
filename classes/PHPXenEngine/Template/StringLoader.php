<?php

namespace PHPXenEngine\Template;

/**
 * StringLoader.php
 *
 * This is an interface for loading language-dependent string constants from anywhere
 * using the implemented function {@link StringLoader::getStr()}.
 * See also {@link VisualBlock::setStrLoader()}.
 *
 * @package PHPXenEngine
 * @author Sergey S. <def.fremd@gmail.com>
 * @version v 0.1 (2022)
 */
interface StringLoader
{

    /**
     * Function returns string by string identifier.
     * @return string|null some text
     */
    public function getStr(string $str_id): ?string;

} // End of interface TemplateLoader