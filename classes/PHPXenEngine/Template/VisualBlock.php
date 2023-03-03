<?php

namespace PHPXenEngine\Template;

use PHPXenEngine\Template\TemplateLoader as TemplateLoader;
use PHPXenEngine\Template\TemplateFileLoader as TemplateFileLoader;
use PHPXenEngine\Template\StringLoader as StringLoader;
use PHPXenEngine\Template\VisualBlockCallBack as VisualBlockCallBack;

/**
 * VisualBlock.php
 *
 * VisualBlock is a class of simple PHP text generator from text template.
 * Supports to set constant strings, variables of block, including from objects (files and
 * other VisualBlock), making sub-block from the one template, enable/disable blocks,
 * setting block repeats, setting static part of template (prefix and suffix),
 * callback functions, searching variables in parents blocks etc.
 *
 * @package PHPXenEngine
 * @author Sergey S. <def.fremd@gmail.com>
 * @version v 0.1 (2022)
 *
 */
class VisualBlock
{
    /**
     * Value of maximum repeat times of block. See function {@link VisualBlock::setCounter()}.
     */
    public const MAX_REPEAT = 256;

    /**
     * Prefix of variable in template.
     */
    public const VAR_PREFIX = 'VAR:';

    /**
     * Prefix of string constant in template.
     */
    public const STR_PREFIX = 'STR:';

    /**
     * The prefix of the inner sub-blocks of the same template: marks the beginning of the sub-blocks.
     * See {@link VisualBlock::makeSubBlocks()}.
     */
    private const SUB_BEGIN_PREFIX = 'BEGIN:';

    /**
     * The prefix of the inner sub blocks of the same template: marks the ending of the sub-blocks.
     * See {@link VisualBlock::makeSubBlocks()}.
     */
    private const SUB_END_PREFIX = 'END:';

    /**
     * Reset mode (by default) - disables reparsing block.
     * The template is parsed once: repeated calling the {@link VisualBlock::out()} or
     * {@link VisualBlock::__toString()} functions will return the cached value.
     */
    public const RESET_OFF = 0;

    /**
     * Reset mode - enables reparsing block.
     * The functions {@link VisualBlock::out()} and {@link VisualBlock::__toString()} returns parsed template each time.
     */
    public const RESET_ON = 1;

    /**
     * Reset mode - enables reparsing including all sub-blocks.
     * It can be logically combined with {@link VisualBlock::RESET_ON}.
     * Usage: $vb->setResetMode(RESET_ON | RESET_GLOBAL)
     */
    public const RESET_GLOBAL = 2;

    /**
     * The default opening tag for a variable or constant string.
     */
    public const DEFAULT_BEGIN_TAG = '{{';

    /**
     * The default closing tag for a variable or constant string.
     */
    public const DEFAULT_END_TAG = '}}';

    // pattern of enabled names
    private const NAME_ID_PATTERN = '([a-zA-Z][a-zA-Z_\d]+)';

    private const EMPTY_STR = '';

    private string $beginTag; // opening string id for var, strings etc
    private string $endTag; // closing string id for var, strings etc
    private array $vars; // array of vars
    private int $counter; // repeat counter
    private ?VisualBlock $parent; // parent of this block or null
    private ?VisualBlockCallBack $callbackObj; // callback object or null

    private bool $isEnabled; // is block enabled
    private bool $isParsed; // is block parsed
    private string $parsedStr; // cache of parsed template

    private $prefix; // static prefix of block
    private $suffix; // static suffix of block
    private ?string $template; // template string
    private ?TemplateLoader $templateLoader; // TemplateLoader or null
    private ?StringLoader $strLoader; // StringLoader or null
    private int $resetMode; // reset mode
    private bool $shiftArrayValue; // true to shift var array pointed
    private bool $searchParentVar; // true for searching for variables in parent blocks
    private string $name; // internal name of block

    private static int $blockCounter = 0; // class implemented counter

    /**
     * Constructor.
     * @param mixed $template template string or {@link TemplateLoader} object
     * @param StringLoader|null $srtLoader object of {@link StringLoader} or null
     */
    function __construct($template = null, ?StringLoader $srtLoader = null)
    {
        // generate block name
        $fullName = explode('\\', __CLASS__);
        $this->name = end($fullName) . self::$blockCounter++;

        $this->vars = [];
        $this->setParent(null);
        $this->setTemplate($template);
        $this->setStrLoader($srtLoader);
        $this->disableShiftArrayPointer();
        $this->setCallback();
        $this->setSearchParentVar(true);
        $this->setResetMode(self::RESET_OFF);
        $this->setCounter(1);
        $this->setPrefixSuffix();
        $this->setTags();
    }

    /**
     * The function sets a template for this instance of the block.
     * @param string|TemplateLoader $template string with a template or
     * an object of the {@link TemplateLoader} class
     * @return $this reference to this instance of the class
     */
    public function setTemplate($template): self
    {
        if (is_null($template)) {
            $this->template = self::EMPTY_STR;
            $this->disable();
        } else if ($template instanceof TemplateLoader) {
            $this->templateLoader = $template;
            $this->template = null;
            $this->enable();
        } else {
            $this->templateLoader = null;
            $this->template = strval($template);
            $this->enable();
        }
        $this->reset(true);
        return $this;
    }

    /**
     * Function returns current template string.
     * @return string template string
     */
    public function getTemplate(): string
    {
        return $this->template ?? $this->template = $this->templateLoader->get();
    }

    /**
     * Sets StringLoader to load string constants. For example, language-dependent content.
     * @param StringLoader|null $srtLoader object realising StringLoader interface
     * @return $this reference to this instance of the class
     */
    public function setStrLoader(?StringLoader $srtLoader): self
    {
        $this->strLoader = $srtLoader;
        return $this;
    }

    /**
     * Returns StringLoader object.
     * @return StringLoader|null object realising StringLoader interface or null.
     */
    public function getStrLoader(): ?StringLoader
    {
        return $this->strLoader;
    }

    /**
     * Sets string prefix of block.
     * @param string|VisualBlock $prefix string of prefix
     * @return $this reference to this instance of the class
     */
    public function setPrefix($prefix): self
    {
        if (is_string($prefix) || $prefix instanceof VisualBlock) {
            $this->prefix = $prefix;
        }
        return $this;
    }

    /**
     * Gets string prefix of block.
     * @return string|VisualBlock string prefix
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Sets string suffix of block.
     * @param string|VisualBlock $suffix string suffix
     * @return $this reference to this instance of the class
     */
    public function setSuffix($suffix): self
    {
        if (is_string($suffix) || $suffix instanceof VisualBlock) {
            $this->suffix = $suffix;
        }
        return $this;
    }

    /**
     * Gets string suffix of block.
     * @return string|VisualBlock string suffix
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * Sets string prefix and suffix of block.
     * @param string|VisualBlock|null $prefix string prefix
     * @param string|VisualBlock|null $suffix string of suffix (if null then $suffix = $prefix)
     * @return $this reference to this instance of the class
     */
    public function setPrefixSuffix($prefix = self::EMPTY_STR, $suffix = null): self
    {
        return $this->setPrefix($prefix)->setSuffix($suffix ?? $prefix);
    }

    /**
     * Sets the tag before strings or vars ID.
     * By default is {@link self::DEFAULT_BEGIN_TAG}
     * @param string $beginTag begin string id tag
     * @return $this reference to this instance of the class
     */
    public function setBeginTag(string $beginTag): self
    {
        $this->beginTag = $beginTag;
        return $this;
    }

    /**
     * Sets the tag after strings or vars ID.
     * By default, is {@link self::DEFAULT_END_TAG}
     * @param string $endTag end string id tag
     * @return $this reference to this instance of the class
     */
    public function setEndTag(string $endTag): self
    {
        $this->endTag = $endTag;
        return $this;
    }


    /**
     * Sets both tags. See {@link setBeginTag()} and {@link endBeginTag()}
     * @param string $beginTag begin string id tag
     * @param string $endTag end string id tag
     * @return $this
     */
    public function setTags(string $beginTag = self::DEFAULT_BEGIN_TAG, string $endTag = self::DEFAULT_END_TAG):
    self
    {
        return $this->setBeginTag($beginTag)->setEndTag($endTag);
    }

    /**
     * Returns internal name of this block.
     * @return string name of block
     */
    public function getName(): string
    {
        return $this->name;
    }

    /*
     * Sets name of this block.
     * For internal use. The name reflects the hierarchy of blocks.
     */
    private function setName($name): self {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets parent of this block.
     * When a variable is set as an external VisualBlock or user calls function {@link makeBlocks()}
     * then the parent of sub-block is set automatically.
     * @param VisualBlock|null $parent parent object of this block
     * @return $this reference to this instance of the class
     */
    public function setParent(?VisualBlock $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Returns the parent block of this block if it is present.
     * @return VisualBlock|null the parent block of this block or null
     */
    public function getParent(): ?VisualBlock
    {
        return $this->parent;
    }

    /**
     * Function helps to create variable as external file by auto-create {@link TemplateFileLoader}.
     * @param string $varName variable name
     * @param string $fileName external file name
     * without file extension, see {@link TemplateFileLoader::getExtensions()}
     * @param string $path the path where the file is located
     * @return $this reference to this instance of the class
     */
    public function setVarFile(string $varName, string $fileName = '', string $path = ''): self
    {
        $this->setVar($varName, new TemplateFileLoader(
            empty($fileName) ? $varName : $fileName,
            empty($path) && !empty($this->templateLoader) && $this->templateLoader instanceof TemplateFileLoader ?
                $this->templateLoader->getPath() : $path
        ));
        return $this;
    }

    /**
     * The function sets the variable of the block.
     * Usage in Template: '...{{VAR:name1}}...{{VAR:name2}}...'
     * @param string $name variable name
     * @param mixed $val variable value
     * @return $this reference to this instance of the class
     */
    public function setVar(string $name, $val): self
    {
        if (($this->vars[$name] = $val) instanceof VisualBlock) {
            $val->setParent($this)->setName($this->getName() . '.' . $name);
        }
        return $this;
    }

    /**
     * Magic function to set variable of block. Do not use this function directly!
     * To set a variable of the block, use the usual assignment.
     * Usage in PHP code: $visualBlock->varName = value_1;
     * Usage in Template: '...{{VAR:name1}}...{{VAR:name2}}...'
     * @param string $name variable name
     * @param mixed $val variable value
     * @return void
     */
    public function __set(string $name, $val): void
    {
        $this->setVar($name, $val);
    }

    /**
     * Sets the values of several variables from an associative array.
     *
     * Usage 1:
     * In template: '{{VAR:name1}}...{{VAR:name2}}...{{VAR:name2}}...'
     * In code: $vars = ['name1' => value1, 'name2' => value2, 'name3' => value3 ...];
     *          $vb->setVars($vars);
     *
     * Usage 2:
     * In template: '{{VAR:form_public_name}}...{{VAR:form_public_sex}}...
     *               {{VAR:form_private_age}}...{{VAR:form_private_mail}}...'
     * In code: $vars = ['form' => [
     *                      'public'=> ['name'=>'Smith','sex'=>'male'],
     *                      'private'=>['age'=>48, 'mail'=>'Mr.Smith(at)mail.gov']
     *                  ]];
     *           $vb->setVars($vars);
     * @return $this reference to this instance of the class
     */
    public function setVars(array $vars, ?string $prefix = null): self
    {
        foreach ($vars as $name => $var) {
            $fullName = isset($prefix) ? $prefix . '_' . $name : $name;
            if (is_array($var)) {
                $this->setVars($var, $fullName);
            } elseif (!empty($fullName)) {
                $this->setVar($fullName, $var);
            }
        }
        return $this;
    }

    /*
     * The function returns reference to the block variable.
     * For internal use or in an extended class.
     */
    protected function &getVar(string $name)
    {
        if (isset($this->vars[$name])) return $this->vars[$name];

        // search in parent is enabled, the parent is set, variable isn't this object (exclude self looping)
        if ($this->searchParentVar && isset($this->parent) && ($var = $this->parent->getVar($name)) !== $this) {
            return $var;
        }

        $noVar = null;
        return $noVar;
    }

    /**
     * Magic function to get reference of block variable. Do not use this function directly!
     * In case of failure and if there is a parent block, requests a variable
     * with the same name in the parent block.
     * To get variables of the block, use the usual assignment:
     * <code>
     * Usage:
     * $vBlock->varName++; // increment of block variable
     * $vBlock->varName += 100; // adding the number 100
     * </code>
     * @param string $name variable name
     * @return mixed|null variable reference
     */
    public function &__get(string $name)
    {
        return $this->getVar($name);
    }

    /**
     * Returns link of all variables array
     * @return array - variables array
     */
    public function getVars(): array
    {
        return $this->vars;
    }

    /**
     * Magic function to check variable of only this block. Do not use this function directly!
     * <code>
     * Usage: isset($vBlock->varName);
     * </code>
     * @param string $name variable name
     * @return bool is true if variable is set
     */
    public function __isset(string $name): bool
    {
        return isset($this->vars[$name]);
    }

    /**
     * Magic function to unset variable of ony this block. Do not use this function directly!
     * <code>
     * Usage: unset($vBlock->varName);
     * </code>
     * @param string $name variable name
     */
    public function __unset(string $name)
    {
        if (isset($this->vars[$name])) {
            unset($this->vars[$name]);
        }
    }

    /**
     * Clears all variables of this block.
     * @return $this reference to this instance of the class
     */
    public function clearVars(): self
    {
        foreach (array_keys($this->vars) as $name) unset($this->$name);
        return $this;
    }

    /**
     * Sets current position of array variable of this block.
     * It can be both numeric and string indexes.
     * <code>
     * Usage:
     * VariableBlock::setVarArrayIndex($visualBlock->arrayVar1, 1);
     * VariableBlock::setVarArrayIndex($visualBlock->arrayVar2, 'string_index');
     * </code>
     * @param int|string $index new index of variable
     * @return mixed reference to array variable
     */
    public static function &setVarArrayIndex(&$variable, $index)
    {
        if (isset($variable) && is_array($variable)) {
            reset($variable);
            if (is_string($index)) {
                do {
                    if (strcmp(key($variable), $index) == 0) break;
                } while (next($variable) !== false && !is_null(key($variable)));

            } elseif (is_numeric($index) && $index < count($variable)) {
                while ($index--) next($variable);
            }
        }
        return $variable;
    }

    /**
     * Creates sub-VisualBlocks and from the same template body.
     * Inside parent template this sub-blocks should be noted by ids 'BEGIN:block_name'/'END:block_name' and
     * surrounded by tags (see functions {@link VisualBlock::setBeginTag()} and {@link VisualBlock::setEndTag()}).
     * The (sub-)blocks can be many times enclosed to each other.
     * Sample:
     * <code>
     * {{BEGIN:block_outer}}Test of outer block.
     *     {{BEGIN:block_inner}}Text of inner block.{{END:block_inner}}
     * {{END:block_outer}}
     * </code>
     *
     * @return $this reference to this instance of the class
     */
    public function makeSubBlocks(): self
    {
        // loads template from external source if it hasn't been done yet
        if (is_null($this->template)) $this->getTemplate();

        $posCur = 0;
        $beginBlockStrID = $this->beginTag . self::SUB_BEGIN_PREFIX . self::NAME_ID_PATTERN . $this->endTag;
        while (preg_match('/' . $beginBlockStrID . '/', $this->template, $matches, PREG_OFFSET_CAPTURE, $posCur)) {

            // sets where cutting inside template starts
            $cutStartInside = $matches[0][1] + strlen($matches[0][0]);

            // looking for where cutting inside template ends
            $endBlockStrID = $this->beginTag . self::SUB_END_PREFIX . $matches[1][0] . $this->endTag; // closing tag
            if (!($cutEndInside = strpos($this->template, $endBlockStrID, $cutStartInside))) {
                $cutEndInside = null;
            }

            // making new subBlock
            $subBlock = new VisualBlock(substr($this->template, $cutStartInside,
                $cutEndInside - $cutStartInside), $this->strLoader);
            $this->setVar($matches[1][0], $subBlock);
            $subBlock->makeSubBlocks();

            // sets current position behind subBlock
            $posCur = is_null($cutEndInside) ? strlen($this->template) : $cutEndInside + strlen($endBlockStrID);
            // cutout subBlock template body
            $this->template = substr($this->template, 0, $matches[0][1]) . // before subBlock
                $this->beginTag . self::VAR_PREFIX . $matches[1][0] . $this->endTag . // change to {VAR:var-name}
                substr($this->template, $posCur); // behind subBlock
        }
        return $this;
    }

    /**
     * Returns all variables which is instance of VisualBlock objects.
     * @return array object variables
     */
    public function getAllSubBlocks(): array
    {
        $subBlocks = [];
        foreach ($this->vars as $name => $block) {
            if ($block instanceof VisualBlock) $subBlocks[$name] = $block;
        }
        return $subBlocks;
    }

    /**
     * Sets repeat counter of current block.
     * See also {@link VisualBlock::setCallback()} and {@link VisualBlock::setResetMode()).
     * @param int $counter int number of repeating this block
     * @return $this reference to this instance of the class
     */
    public function setCounter(int $counter): self
    {
        if ($counter == 0) {
            $this->counter = 1;
            $this->disable();
        }
        if ($counter >= 1 && $counter <= self::MAX_REPEAT) {
            $this->counter = $counter;
            if (!$this->isEnabled()) $this->enable();
        }
        return $this;
    }

    /**
     * Returns repeat counter of current block. See {@link VisualBlock::setCounter()}.
     * @return int number of repeats
     */
    public function getCounter(): int
    {
        return $this->counter;
    }

    /**
     * Enable this block.
     * @param bool $enable is true if this block should be enabled, false otherwise.
     * @return $this reference to this instance of the class
     */
    public function enable(bool $enable = true): self
    {
        $this->isEnabled = $enable;
        return $this;
    }

    /**
     * Disable this block.
     * Disabled blocks will never be parsed and returns an empty string.
     * @return $this reference to this instance of the class
     */
    public function disable(): self
    {
        return $this->enable(false);
    }

    /**
     * Checks is block enabled.
     * See function {@link VisualBlock::enable()} and {@link VisualBlock::disable()}.
     * @return bool is true if this block enabled, false otherwise.
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Checks is block parsed or not.
     * @return bool true if block has already been parsed, false otherwise.
     */
    public function isParsed(): bool
    {
        return $this->isParsed;
    }

    /**
     * Resets cached result of parsed block.
     * @param bool $global is true when should reset all sub-blocks too.
     * @return $this reference to this instance of the class
     */
    public function reset(bool $global = false): self
    {
        $this->parsedStr = self::EMPTY_STR;
        $this->isParsed = false;

        if ($global) { // including sub-blocks
            foreach ($this->vars as $var) {
                if ($var instanceof VisualBlock) $var->reset($global);
            }
        }
        return $this;
    }

    /**
     * Sets reparsing mode of the block. By default {@link VisualBlock::RESET_OFF}.
     * If enabled the functions {@link VisualBlock::out()} and {@link VisualBlock::__toString()} returns a
     * new reparsed data every call.
     * @param int $mode parsing mode:
     *      it can be {@link VisualBlock::RESET_OFF}, {@link VisualBlock::RESET_ON} or logic
     *      combination of {@link VisualBlock::RESET_ON} | {@link VisualBlock::RESET_GLOBAL}
     * @return $this reference to this instance of the class
     */
    public function setResetMode(int $mode): self
    {
        $this->resetMode = $mode;
        return $this;
    }

    /**
     * Returns reparsing mode of the block. See {@link VisualBlock::setResetMode()}.
     * @return int value of integer corresponding current reparsing mode.
     */
    public function getResetMode(): int
    {
        return $this->resetMode;
    }

    /*
     * Calls before parsing template for checking reset mode.
     */
    private function autoReset(): void
    {
        if ($this->resetMode & self::RESET_ON) {
            $this->reset($this->resetMode & self::RESET_GLOBAL);
        }
    }

    /*
     * Main parsing function.
     */
    private function parse(): string
    {
        // initialisation of current counter
        $counter = $this->counter;
        do {
            // if a callback object is registered, then first call the callback function of this object
            if (isset($this->callbackObj)) {
                // if callback returns false then skipping this cycle
                if (!$this->callbackObj->callback($this, $this->counter - $counter + 1)) continue;
            }

            // copy the template to a temporary variable
            $curParsing = $this->getTemplate();

            // if the StringLoader registered then we replace all occurrences 'STR:str_id'
            if (isset($this->strLoader)) {
                $curParsing = preg_replace_callback(
                    '/' . $this->beginTag . self::STR_PREFIX . self::NAME_ID_PATTERN . $this->endTag . '/',
                    function ($m) {
                        return $this->strLoader->getStr($m[1]) ?? $m[0];
                    },
                    $curParsing
                );
            }

            // Replaces all occurrences of 'VAR:id_var' by the specified values of variables
            $curParsing = preg_replace_callback(
                '/' . $this->beginTag . self::VAR_PREFIX . self::NAME_ID_PATTERN . $this->endTag . '/',
                function ($m) use ($counter) {
                    return $this->getValue($m[1], $counter) ?? $m[0];
                },
                $curParsing
            );

            // Adds to resulting parsed string
            $this->parsedStr .= $curParsing;

        } while (--$counter);

        // parsing done
        $this->isParsed = true;

        // resulting string
        return $this->parsedStr;
    }

    /*
     * Returns value of block variable.
     * If variable value is a string and first char is '$' then tries to get string by StringLoader.
     * If variable value is an array and '$this->>shiftArrayValue == true' then after getting the value the
     * pointer of array will be shifted to the next value.
     */
    protected function getValue(
        $varName, // link to current var
        int $counter // repeat counter
    ): ?string
    {
        $var = &$this->$varName;
        if (is_string($var)) {
            $value = strlen($var) > 1 && $var[0] == '$' && isset($this->strLoader) &&
            !is_null($strConst = $this->strLoader->getStr(substr($var, 1))) ? $strConst : $var;
        } elseif (is_array($var)) {
            $value = !$this->shiftArrayValue || $this->counter == $counter ? current($var) : next($var);
            if (key($var) === null && $value === false) $value = reset($var);
        } elseif (is_bool($var)) {
            $value = $var ? 'true' : 'false';
        } else {
            $value = $var;
        }
        return !is_bool($value) ? $value : ($value ? 'true' : 'false');
    }

    /**
     * Enables shift array pointer to next value when repeat of block.
     * By defaults is disabled.
     * See also {@link setCounter()}.
     * @return $this reference to this instance of the class
     */
    public function enableShiftArrayPointer(): self
    {
        $this->shiftArrayValue = true;
        return $this;
    }

    /**
     * Disables shift array pointer to next value when repeat of block.
     * See {@link enableShiftArrayPointer()}.
     * @return $this reference to this instance of the class
     */
    public function disableShiftArrayPointer(): self
    {
        $this->shiftArrayValue = false;
        return $this;
    }

    /**
     * Checks if shifting array pointer to next value is enabled.
     * See {@link enableShiftArrayPointer()}.
     * @return bool
     */
    public function isShiftArrayValue(): bool
    {
        return $this->shiftArrayValue;
    }

    /**
     * Sets callback object ({@link VisualBlockCallBack}) which function calls every parsing works.
     * Function calls every repeating. See {@link setCounter()}.
     * @param VisualBlockCallBack|null $callbackObj callback object or null
     * @return VisualBlockCallBack|null callback object or null
     */
    public function setCallback(?VisualBlockCallBack $callbackObj = null): ?VisualBlockCallBack
    {
        return $this->callbackObj = $callbackObj;
    }

    /**
     * Using to return result of parsing work.
     * This is a magic function that is automatically called when an object is converted to a string.
     * @return string result of parsing work
     */
    public function __toString(): string
    {
        if ($this->isEnabled) {
            $this->autoReset(); // сбрасываем, если задано
            return $this->prefix . ($this->isParsed ? $this->parsedStr : $this->parse()) . $this->suffix;
        }
        return $this->prefix . self::EMPTY_STR . $this->suffix;
    }

    /**
     * Output this block by echo command to standard output stream.
     * @return $this reference to this instance of the class
     */
    public function out(): self
    {
        echo $this;
        return $this;
    }

    /**
     * Enables or disables requesting a variable from the parent block,
     * if this variable is not found in this block.
     * @param bool $searchParentVar is true if enable
     * @return $this reference to this instance of the class
     */
    public function setSearchParentVar(bool $searchParentVar): self
    {
        $this->searchParentVar = $searchParentVar;
        return $this;
    }

    /**
     * Checks if is enabled requesting a variable from the parent block.
     * See {@link setSearchParentVar()}.
     * @return bool
     */
    public function isSearchParentVar(): bool
    {
        return $this->searchParentVar;
    }
} // End of class VisualBlock