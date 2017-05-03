<?php

namespace Datto\PHPUnit\Entropy;

use \PHPUnit_Framework_TestListener;
use \PHPUnit_Framework_Test;
use \PHPUnit_Framework_TestSuite;
use \PHPUnit_Framework_AssertionFailedError;
use \Exception;

/**
 * An adapter to work with versions of PHPUnit prior to 6
 *
 * @package Datto\PHPUnit\Entropy
 */
class Listener extends BaseListener implements \PHPUnit_Framework_TestListener
{
    /**
     * @inheritdoc
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->setHasErrored();
    }

    /**
     * @inheritdoc
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->setHasErrored();
    }

    /**
     * @inheritdoc
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->startSuite($suite);
    }

    /**
     * @inheritdoc
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->endSuite();
    }

    /**
     * Unused public functions
     */

    /**
     * @inheritdoc
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * @inheritdoc
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * @inheritdoc
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * @inheritdoc
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
    }

    /**
     * @inheritdoc
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
    }
}
