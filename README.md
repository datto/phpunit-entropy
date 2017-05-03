# Datto PHPUnit Entropy

Entropy is a library to provide tools for working with randomized testing in
PHPUnit as well as for testing systems that make use of PHP's random functions
such as `rand` and `shuffle`.

## Use cases

### Detect dependent tests

Sometimes unit tests require changes to global state, or cover functionality
that alters that state. While this is never an ideal situation, this can often
result in the creation of inadvertent dependencies on other tests.
 
Enabling Entropy's test shuffling functionality randomizes the order of your
tests, helping to highlight these dependencies.

Entropy's test-shuffling also avoids changing the order of tests using the
`@depends` functionality of PHPUnit; future work will work to ensure that
shuffling can still occur for these tests when appropriate.

### Randomized testing

Sometimes you can find yourself in a situation where the range of inputs for a
function is so large that taking a cross-section of those inputs is a more
efficient use of your resources. But what happens if that cross-section has
holes where your tests might fail?

One way to get around this is to use randomized inputs, where input is
generated in such a way as to get a random cross-section of the ranges. In
other words, using `rand` or another such non-deterministic approach.

An extension of this, property-based testing is the practice of applying random
input to your application or function, and observing that the output adheres to
certain rules or has certain properties relative to the input, rather than
precise testing of equality to a known result set.

By managing the random seed for your tests, Entropy allows not only the use of
these approaches, but also to make them repeatable, either by accepting a fixed
seed or recovering it from the last failed run until your test suite passed.

## Including in your project

You can add this library to your project using Composer:

```shell
$ composer require datto/phpunit-entropy
```

## Running

Once configured, you can run your test suite as normal; the listener will look
after itself. On test error or failure, the seed used will be stored in a
temporary file, so that on the next run it will be reused, rather than a new
seed generated.

### Defining the seed

The seed for the random number generator is provided from up to four locations,
presented here in priority order.

#### Environment variable

If set, the `SEED` environment variable will be used to override any other
settings. It can be set via `export`, but recommended use is to set it for the
current run only:

```shell
SEED=123456 phpunit -c phpunit.xml tests
```

#### Configuration

The seed may be fixed by configuration; see the [Configuration](#Configuration)
section below.

#### Last run

If a test run fails, the seed used is stored in a temporary file. This is then
loaded on a subsequent test, and will persist until the suite succeeds again.

#### rand()

If no seed is set via the above methods, the final method is to use PHP's
`rand` function.

## Configuration

### Entropy Test Listener

Once installed via composer, configuring the test listener is simply a matter
of altering your PHPUnit configuration file (often `phpunit.xml`):

```xml
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/4.1/phpunit.xsd"
  backupGlobals="false"
  colors="true"
  bootstrap="bootstrap.php"
  >
    <listeners>
        <listener class="Datto\PHPUnit\Listener\Entropy\Listener">
            <arguments>
                <array>
                    <element key="seeding">
                        <array>
                            <element key="enabled">
                                <boolean>true</boolean>
                            </element>
                            <element key="seed">
                                <integer>1234567</integer>
                            </element>
                            <element key="file">
                                <string>/tmp/phpentropy-seed</string>
                            </element>
                        </array>
                    </element>
                    <element key="shuffle">
                        <boolean>true</boolean>
                    </element>
                </array>
            </arguments>
        </listener>
    </listeners>
</phpunit>
```

#### Arguments

##### seeding

###### enabled - boolean

If set to true, the random number generator will be seeded by the listener.

###### seed - integer

If you set the seed via this argument, only this value will be used to seed
the random number generator. See the section on seed priority below.

###### file - string

If set, this file will be used to store the last failed random seed; it
defaults to `[TMPDIR LOCATION]/phpunit-entropy-seed`.

##### shuffle - boolean

If set to true, the order in which unit tests are executed will be randomized
(except for suites where `@depends` is in use). This is useful in determining
and identifying inter-test dependencies.

## Future work

 * Keep track of subsequent test runs
 * Detect changes between runs
 * Provide guiding output when tests are shuffled

## Developer contact

Christopher Hoult <[choult@datto.com](mailto://choult@datto.com)>
