<?php

namespace PHPXenEngine\Template;

/**
 * VisualBlockCallBack.php
 *
 * This interface is designed to implement callback capabilities
 * in the class {@link VisualBlock).
 * For more information see {@link VisualBlock::setCallback()).
 *
 * @package PHPXenEngine
 * @author Sergey S. <def.fremd@gmail.com>
 * @version v 0.1 (2022)
 */
interface VisualBlockCallBack
{

    /**
     * Callback function, see {@link VisualBlock::setCallback()).
     * @param VisualBlock $block - the block that calls function
     * @param int $iteration - current number of iteration (from 1 to {@link VisualBlock::getCounter()})
     * @return bool true if continue parsing, false to interrupting
     */
    public function callback(VisualBlock $block, int $iteration): bool;

} // End of interface VisualBlockCallBack
