<?php

namespace Datto\PHPUnit\Entropy\Tests;

use Datto\PHPUnit\Entropy\BaseListener as Listener;

class ConcreteListener extends Listener
{
    public $seedSet;

    public $setSeedTimes = 0;

    public $output = '';

    /**
     * @inheritDoc
     */
    protected function setSeed($seed)
    {
        $this->seedSet = $seed;
        $this->setSeedTimes++;
    }

    /**
     * @inheritDoc
     */
    protected function writeLn($line, $underlineCharacter = null)
    {
        $this->output .= \implode(' ', array($line, $underlineCharacter));
    }

    /**
     * @inheritDoc
     */
    public function setHasErrored()
    {
        parent::setHasErrored();
    }

    /**
     * @inheritDoc
     */
    public function startSuite($suite = null)
    {
        parent::startSuite($suite);
    }

    /**
     * @inheritDoc
     */
    public function endSuite()
    {
        parent::endSuite();
    }
}
