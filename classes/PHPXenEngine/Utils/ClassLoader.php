<?php

namespace PHPXenEngine\Utils;

/* Uncomment line below to debug */
//define('DEBUG', 1);

/**
 * This class allows automatically load other classes of the project.
 *
 * @package PHPXenEngine
 * @author Sergey S. <def.fremd@gmail.com>
 * @version v 0.1 (2022)
 */
class ClassLoader
{
    private static ?ClassLoader $classLoader = null;

    private static string $baseDir;

    private function __construct($baseDir = '')
    {
        self::$baseDir = $baseDir;
        spl_autoload_register(array($this, 'loader'));
    }

    private function loader($className)
    {
        $file = realpath(self::$baseDir . DIRECTORY_SEPARATOR .
            str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php');

        if (is_readable($file)) {

            require_once $file;

            if (defined('DEBUG')) {
                error_log("Class loaded: " . $className);
            }
        } elseif (defined('DEBUG')) {
            error_log('Error: class ' . $className . ' is not found at ' . $file);
            exit(1);
        }
    }

    public static function init(): ClassLoader
    {
        return is_null(self::$classLoader) ? (self::$classLoader = new ClassLoader(CLASS_LOADER_PATH)) :
            self::$classLoader;
    }

    public function getBaseDir(): string
    {
        return self::$baseDir;
    }
}
