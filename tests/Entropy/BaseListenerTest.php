<?php

namespace Datto\PHPUnit\Entropy\Tests;

use \PHPUnit\Framework\TestCase as TestCase;
use \Datto\PHPUnit\Entropy\BaseListener as Listener;

/**
 * @coversDefaultClass \Datto\PHPUnit\Entropy\BaseListener
 */
class BaseListenerTest extends TestCase
{

    /**
     * @covers ::hasErrored
     * @covers ::setHasErrored
     */
    public function testHasErrored()
    {
        $listener = new ConcreteListener();
        $this->assertFalse($listener->hasErrored());
        $listener->setHasErrored();
        $this->assertTrue($listener->hasErrored());
    }

    /**
     * DataProvider for testStartSuite
     *
     * @return array
     */
    public function startSuiteProvider()
    {
        $fname = \tempnam(\sys_get_temp_dir(), '');
        $seed = 707;
        \file_put_contents($fname, $seed);

        return array(
            'ENV' => array(
                'envSeed' => 101,
                'options' => array(
                    Listener::OPTION_KEY_SEEDING => array(
                        Listener::OPTION_KEY_ENABLED => true,
                        Listener::OPTION_KEY_SEEDFILE => '/tmp/nofile'
                    )
                ),
                'expectedMessage' => 'environment',
                'expetedSeed' => 101
            ),
            'ENV with options' => array(
                'envSeed' => 101,
                'options' => array(
                    Listener::OPTION_KEY_SEEDING => array(
                        Listener::OPTION_KEY_ENABLED => true,
                        Listener::OPTION_KEY_SEED => 202,
                        Listener::OPTION_KEY_SEEDFILE => '/tmp/nofile'
                    )
                ),
                'expectedMessage' => 'environment',
                'expetedSeed' => 101
            ),
            'Options with seed' => array(
                'envSeed' => null,
                'options' => array(
                    Listener::OPTION_KEY_SEEDING => array(
                        Listener::OPTION_KEY_ENABLED => true,
                        Listener::OPTION_KEY_SEED => 202,
                        Listener::OPTION_KEY_SEEDFILE => '/tmp/nofile'
                    )
                ),
                'expectedMessage' => 'config',
                'expetedSeed' => 202
            ),
            'Options with seed file' => array(
                'envSeed' => null,
                'options' => array(
                    Listener::OPTION_KEY_SEEDING => array(
                        Listener::OPTION_KEY_ENABLED => true,
                        Listener::OPTION_KEY_SEEDFILE => $fname
                    )
                ),
                'expectedMessage' => 'last stored seed',
                'expectedSeed' => $seed
            ),
            'No env or options' => array(
                'envSeed' => null,
                'options' => array(
                    Listener::OPTION_KEY_SEEDING => array(
                        Listener::OPTION_KEY_ENABLED => true,
                        Listener::OPTION_KEY_SEEDFILE => '/tmp/nofile'
                    )
                ),
                'expectedMessage' => 'Generated new seed',
            )
        );
    }

    /**
     * @dataProvider startSuiteProvider
     *
     * @covers ::startSuite
     * @covers ::__construct
     * @covers ::setOptions
     *
     * @param mixed $envSeed
     * @param array $options
     * @param string $expectedMessage
     * @param null $expectedSeed
     */
    public function testStartSuite($envSeed, array $options, $expectedMessage, $expectedSeed = null)
    {
        putenv("SEED=$envSeed");

        $listener = new ConcreteListener($options);
        $listener->startSuite();

        if ($expectedSeed) {
            $this->assertEquals($expectedSeed, $listener->seedSet);
        } else {
            $this->assertTrue($listener->seedSet > 0);
        }

        $this->assertRegExp("/{$expectedMessage}/", $listener->output);
    }

    /**
     * DataProvider for testEndSuite
     *
     * @return array
     */
    public function endSuiteProvider()
    {
        $fname = \tempnam(\sys_get_temp_dir(), '');
        $fnameExists = \tempnam(\sys_get_temp_dir(), '');
        $existingSeed = 200;
        \file_put_contents("{$fnameExists}1", $existingSeed);
        \file_put_contents("{$fnameExists}2", $existingSeed);
        return array(
            'Happy path, no seed stored' => array(
                'options' => array(Listener::OPTION_KEY_SEEDING => array(
                    Listener::OPTION_KEY_SEEDFILE => "{$fname}1"
                )),
                'seedFile' => "{$fname}1",
                'suites' => array(
                    false,
                    false,
                    false
                ),
                'expectedHasErrored' => false,
                'expectedSeedStored' => false,
            ),
            'Unhappy path, seed stored' => array(
                'options' => array(Listener::OPTION_KEY_SEEDING => array(
                    Listener::OPTION_KEY_SEEDFILE => "{$fname}2",
                )),
                'seedFile' => "{$fname}2",
                'suites' => array(
                    false,
                    true,
                    false
                ),
                'expectedHasErrored' => true,
                'expectedSeedStored' => true
            ),
            'Unhappy path, existing seed, seed stored' => array(
                'options' => array(Listener::OPTION_KEY_SEEDING => array(
                    Listener::OPTION_KEY_SEEDFILE => "{$fnameExists}1",
                )),
                'seedFile' => "{$fnameExists}1",
                'suites' => array(
                    false,
                    true,
                    false
                ),
                'expectedHasErrored' => true,
                'expectedSeedStored' => true
            ),
            'Unhappy path, existing seed, stored seed wiped' => array(
                'options' => array(Listener::OPTION_KEY_SEEDING => array(
                    Listener::OPTION_KEY_SEEDFILE => "{$fnameExists}2",
                )),
                'seedFile' => "{$fnameExists}2",
                'suites' => array(
                    false,
                    false,
                    false
                ),
                'expectedHasErrored' => false,
                'expectedSeedStored' => false
            )
        );
    }

    /**
     * @dataProvider endSuiteProvider
     *
     * @param array $options
     * @param string $seedFile
     * @param array $suites
     * @param bool $expectedHasErrored
     * @param bool $expectedSeedStored
     */
    public function testEndSuite(array $options, $seedFile, array $suites, $expectedHasErrored, $expectedSeedStored)
    {
        $listener = new ConcreteListener($options);
        foreach ($suites as $error) {
            $listener->startSuite();
            if ($error) {
                $listener->setHasErrored();
            }
        }

        foreach ($suites as $suite) {
            $listener->endSuite();
        }

        $this->assertEquals($expectedHasErrored, $listener->hasErrored());
        if ($expectedSeedStored) {
            $this->assertTrue(\file_exists($seedFile));
            $this->assertEquals($listener->seedSet, \file_get_contents($seedFile));
        } else {
            $this->assertFalse(\file_exists($seedFile));
        }
    }

    /**
     * DataProvider for testShuffleSuite
     *
     * @return array
     */
    public function shuffleSuiteProvider()
    {
        return array(
            'Happy path, shuffle, no deps' => array(
                'options' => array(Listener::OPTION_KEY_SHUFFLE => true),
                'hasDependencies' => false,
                'shouldShuffle' => true
            ),
            'Happy path, no shuffle, no deps' => array(
                'options' => array(Listener::OPTION_KEY_SHUFFLE => false),
                'hasDependencies' => false,
                'shouldShuffle' => false
            ),
            'Happy path, missing shuffle, no deps' => array(
                'options' => array(),
                'hasDependencies' => false,
                'shouldShuffle' => false
            ),
            'Happy path, shuffle, deps' => array(
                'options' => array(Listener::OPTION_KEY_SHUFFLE => true),
                'hasDependencies' => true,
                'shouldShuffle' => false
            ),
        );
    }

    /**
     * @dataProvider shuffleSuiteProvider
     *
     * @param array $options
     * @param bool $hasDependencies
     * @param bool $shouldShuffle
     */
    public function testShuffleSuite(array $options, $hasDependencies, $shouldShuffle)
    {
        $suite = $this->createMock(
            $this->getPHPUnitClass('\PHPUnit\Framework\TestSuite'),
            array('tests', 'setTests'),
            array(),
            '',
            false
        );
        $tests = array();
        for ($i = 0; $i < 10; $i++) {
            $test = $this->createMock(
                $this->getPHPUnitClass('\PHPUnit\Framework\TestCase'),
                array('hasDependencies'),
                array(),
                '',
                false
            );
            $test->expects($this->any())
                ->method('hasDependencies')
                ->will($this->returnValue($hasDependencies));
            $tests[] = $test;
        }

        $suite->expects($this->any())
            ->method('tests')
            ->willReturn($tests);

        $suite->expects(($shouldShuffle) ? $this->once() : $this->never())
            ->method('setTests');

        $listener = new ConcreteListener($options);
        $listener->startSuite($suite);
    }

    /**
     * PHPUnit version interop fun!
     *
     * @param string $className
     *
     * @return mixed
     */
    private function getPHPUnitClass($className)
    {
        if (!class_exists($className)) {
            $className = preg_replace('/^_/', '\\', str_replace('\\', '_', $className));
        }

        return $className;
    }
}
