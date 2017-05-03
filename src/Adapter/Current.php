<?php

namespace Datto\PHPUnit\Entropy;

use PHPUnit\Framework as PHPUnit;
use \Exception;

/**
 * An adapter to work with PHPUnit 6+
 *
 * @package Datto\PHPUnit\Entropy
 */
class Listener extends BaseListener implements PHPUnit\TestListener
{
    /**
     * @inheritdoc
     */
    public function addError(PHPUnit\Test $test, Exception $e, $time)
    {
        $this->setHasErrored();
    }

    /**
     * @inheritdoc
     */
    public function addFailure(PHPUnit\Test $test, PHPUnit\AssertionFailedError $e, $time)
    {
        $this->setHasErrored();
    }

    /**
     * @inheritdoc
     */
    public function startTestSuite(PHPUnit\TestSuite $suite)
    {
        $this->startSuite($suite);
    }

    /**
     * @inheritdoc
     */
    public function endTestSuite(PHPUnit\TestSuite $suite)
    {
        $this->endSuite();
    }

    /**
     * Unused public functions
     */

    /**
     * @inheritdoc
     */
    public function addWarning(PHPUnit\Test $test, PHPUnit\Warning $e, $time)
    {
    }

    /**
     * @inheritdoc
     */
    public function addIncompleteTest(PHPUnit\Test $test, Exception $e, $time)
    {
    }

    /**
     * @inheritdoc
     */
    public function addRiskyTest(PHPUnit\Test $test, Exception $e, $time)
    {
    }

    /**
     * @inheritdoc
     */
    public function addSkippedTest(PHPUnit\Test $test, Exception $e, $time)
    {
    }

    /**
     * @inheritdoc
     */
    public function startTest(PHPUnit\Test $test)
    {
    }

    /**
     * @inheritdoc
     */
    public function endTest(PHPUnit\Test $test, $time)
    {
    }
}
