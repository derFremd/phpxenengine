<?php

namespace PHPXenEngine\Test;

/**
 * Simple test class for checking {@link PHPXenEngine\Utils\ClassLoader} class.
 */
class Test {

    /**
     * @var string string value
     */
    private string $sVal;

    /**
     * @var int integer value
     */
    private int $iVal;

    /**
     * Constructor.
     * @param $sVal input string value
     * @param $iVal input integer value
     */
    public function __construct($sVal = "test", $iVal = 1) {
        $this->sVal = $sVal;
        $this->iVal = $iVal;
    }

    /**
     * @return string is concatenated string and integer values
     */
    public function __toString(): string {
        return $this->sVal + $this->iVal;
    }
}