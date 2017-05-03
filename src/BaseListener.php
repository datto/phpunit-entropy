<?php

namespace Datto\PHPUnit\Entropy;

use \PHPUnit_Framework_Test;
use \PHPUnit_Framework_TestSuite;
use \PHPUnit_Framework_AssertionFailedError;

/**
 * A PHPUnit Test Listener to manage the state of the random number generator
 *
 * @package Datto\PHPUnit\Entropy
 */
abstract class BaseListener
{
    /**
     * Array keys for this Listener's options
     */
    const OPTION_KEY_SEEDING = 'seeding';
    const OPTION_KEY_ENABLED = 'enabled';
    const OPTION_KEY_SEED = 'seed';
    const OPTION_KEY_SEEDFILE = 'file';
    const OPTION_KEY_SHUFFLE = 'shuffle';

    /**
     * The default seed filename
     */
    const DEFAULT_SEED_FILENAME = 'phpunit-entropy-seed';

    /**
     * The key of the environment variable to read a defined key from
     */
    const SEED_ENV_KEY = 'SEED';

    /**
     * An associative array of options
     *
     * @var array
     */
    private $options = array();

    /**
     * The current seed for the RNG
     *
     * @var int
     */
    private $seed;

    /**
     * If true, the test suites is considered to have errored
     *
     * @var bool
     */
    private $hasErrored = false;

    /**
     * If true, this Listener is considered to have been initialized
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * A counter to keep track of the number of test suites encountered by this Listener
     *
     * @var int
     */
    private $suites = 0;

    /**
     * Constructs a new Entropy Test Listener
     *
     * @param array $options    Options as passed in by PHPUnit
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Sets the options for this Listener, merging in the default options
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $defaults = $this->getDefaultOptions();
        $this->options = \array_replace_recursive($defaults, $options);
    }

    /**
     * Returns true if the test suites are considered to have errored
     *
     * @return bool
     */
    public function hasErrored()
    {
        return $this->hasErrored;
    }

    /**
     * Sets whether the test suites are considered to have errored
     */
    protected function setHasErrored()
    {
        $this->hasErrored = true;
    }

    /**
     * Indicates to the Listener that a new test suite has started
     */
    protected function startSuite($suite = null)
    {
        if ($suite
            && !($suite instanceof \PHPUnit_Framework_TestSuite || $suite instanceof \PHPUnit\Framework\TestSuite)
        ) {
            throw new \InvalidArgumentException('Invalid suite passed to ::startSuite, found ' . \get_class($suite));
        }
        $this->suites++;
        $this->initialize();

        if ($suite && $this->getOption(self::OPTION_KEY_SHUFFLE, false)) {
            $this->shuffleSuite($suite);
        }
    }

    /**
     * Indicates to the Listener that a test suite has ended
     */
    protected function endSuite()
    {
        if (--$this->suites == 0) {
            // If pass, wipe stored seed
            if ($this->hasErrored) {
                $this->storeSeed();
            } else {
                $this->clearSeed();
            }
        }
    }

    /**
     * Gets an associative array of default options for this Listener
     *
     * @return array
     */
    private function getDefaultOptions()
    {
        return array(
            self::OPTION_KEY_SEEDING => array(
                self::OPTION_KEY_ENABLED => false,
                self::OPTION_KEY_SEEDFILE => \sys_get_temp_dir() . '/' . self::DEFAULT_SEED_FILENAME,
                self::OPTION_KEY_SEED => null,
            ),
            self::OPTION_KEY_SHUFFLE => false
        );
    }

    /**
     * Gets an option by key name, returning $default if it is undefined
     *
     * @param string $key
     * @param null $default
     *
     * @return mixed|null
     */
    private function getOption($key, $default = null)
    {
        return (\array_key_exists($key, $this->options)) ? $this->options[$key] : $default;
    }

    /**
     * Initializes this Listener
     */
    private function initialize()
    {
        if (!$this->initialized) {
            $seedOptions = $this->getOption(self::OPTION_KEY_SEEDING);
            if ($seedOptions[self::OPTION_KEY_ENABLED]) {
                $seed = $this->getSeed();
                $this->setSeed($seed);
            }
            $this->initialized = true;
        }
    }

    /**
     * Gets the RNG seed for this Listener, loading it in the precedence order:
     *  - Environment variable SEED
     *  - The options passed into this Listener (via the 'seed' key)
     *  - The last seed as stored in the seedfile
     *  - A random seed
     *
     * @return int
     */
    private function getSeed()
    {
        if (!$this->seed) {
            $settings = $this->getOption(self::OPTION_KEY_SEEDING);
            $seeds = array(
                'Loaded seed from environment variable ' . self::SEED_ENV_KEY => getenv(self::SEED_ENV_KEY),
                'Loaded seed from PHPUnit config' => $settings[self::OPTION_KEY_SEED],
                'Loaded last stored seed from ' . $this->getSeedFile() => $this->loadSeed(),
                'Generated new seed from rand()' => \rand(1, \getrandmax())
            );

            $seeds = \array_filter($seeds, function ($seed) {
                return (int)$seed;
            });

            $descriptor = \key($seeds);
            $this->seed = (int)(\current($seeds));
            $this->writeLn('Entropy Listener', '-');
            $this->writeLn(" - {$descriptor}: {$this->seed}\n");
        }

        return $this->seed;
    }

    /**
     * Sets the seed for this Listener
     *  - NB: This is for testing purposes
     *
     * @param $seed
     */
    protected function setSeed($seed)
    {
        \mt_srand($seed);
        \srand($seed);
    }

    /**
     * Outputs a line of content to the console. If $underlineCharacter is provided, an underline of that character will
     * be added
     *
     * @param string $line
     * @param null $underlineCharacter
     */
    protected function writeLn($line, $underlineCharacter = null)
    {
        echo "$line\n";
        if ($underlineCharacter) {
            echo str_repeat($underlineCharacter, strlen($line)) . "\n";
        }
    }

    /**
     * Stores the seed for this Listener in the seed file
     */
    private function storeSeed()
    {
        \file_put_contents($this->getSeedFile(), $this->seed);
    }

    /**
     * Clears the seed file, if it exists
     */
    private function clearSeed()
    {
        $filename = $this->getSeedFile();
        if (\file_exists($filename)) {
            \unlink($filename);
        }
    }

    /**
     * Loads the previously failed seed from the seed file, if it exists
     *
     * @return null|string
     */
    private function loadSeed()
    {
        return (\file_exists($this->getSeedFile())) ? \file_get_contents($this->getSeedFile()) : null;
    }

    /**
     * Gets the path for the seed file
     *
     * @return mixed|null
     */
    private function getSeedFile()
    {
        $settings = $this->getOption(self::OPTION_KEY_SEEDING);
        return $settings[self::OPTION_KEY_SEEDFILE];
    }

    /**
     * Shuffles the underlying tests in the passed Test Suite
     *
     * @param $suite
     */
    private function shuffleSuite($suite)
    {
        $tests = $suite->tests();
        $shouldShuffle = true;
        foreach ($tests as $test) {
            if (\method_exists($test, 'hasDependencies') && $test->hasDependencies()) {
                $shouldShuffle = false;
                break;
            }
        }
        if ($shouldShuffle) {
            \shuffle($tests);
            $suite->setTests($tests);
        }
    }
}
