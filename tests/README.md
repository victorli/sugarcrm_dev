# Unit Tests

These tests are the old "unit" tests.  They will eventually be converted into better tests (true unit tests).

## Running SugarCRM Tests

Before submitting a path for inclusion, you need to run the SugarCRM test suite to check that you didn't broke anything.

### Install PHPUnit

Assuming that you should have your php and pear configured properly (pointing to the correct paths), you just need to confirm by running `pear upgrade` on your favorite terminal program.

Now set the auto discover of channels true on [Pear](http://pear.php.net/) by running:

```bash
$ pear config-set auto_discover 1
```

Install [PHPUnit](http://www.phpunit.de/) package with:

```bash
$ pear install pear.phpunit.de/PHPUnit
```

If you get `install ok` on phpunit and it's dependencies, you should now be able to do `phpunit --version` and see the following output:

```
PHPUnit 3.6.12 by Sebastian Bergmann.
```

#### Issues on MAMP?

Make sure you are pointing to the correct php and pear commands. Just add the following line to the `~/.bash_profile` file:

```
# MAMP pear, php, phpunit, etc. of version php 5.3.x
PATH=/Applications/MAMP/bin/php/php5.3.14/bin:$PATH
```

Next thing is to check if `pear` is well configured, so just run `pear upgrade` on your favorite terminal program.

> Some people are getting the following error:
>
> ```
> Notice: unserialize(): Error at offset 276 of 1133 bytes in Config.php on line 1050
> ERROR: The default config file is not a valid config file > or is corrupted.
> ```
> 
> If you get this error as well, just delete the `pear.conf` file located at `/Applications/MAMP/bin/php/php5.3.14/conf/pear.conf`, and rerun the `pear upgrade` command.
> If you still have that issue, remove your `~/.pearrc` since it might be corrupted as well.

### Run PHPUnit

To run the SugarCRM test suite, install the several flavors of Sugar (CE, PRO, ENT) and run install on each one.

Then, run the test suite from the `tests` root directory of the installed instance with the following command:

```bash
$ phpunit
```

The output should display `OK`. If not, you need to figure out what's going on and if the tests are broken because of your modifications.

> If you want to test a single component type its path after the `phpunit` command, e.g.:
>
> ```bash
> $ phpunit include/SugarOAuth2StorageTest.php
> ```
>
> Run the test suite before applying your modifications to check that they run fine on your configuration.

### Code Coverage

If you add a new feature, you also need to check the code coverage by using the `coverage-html` option:

```bash
$ phpunit --coverage-html=cov/
```

Check the code coverage by opening the generated `cov/index.html` page in a browser.

> The code coverage only works if you have XDebug enabled and all dependencies installed.
