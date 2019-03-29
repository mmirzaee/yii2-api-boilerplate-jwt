<p align="center">
    <a href="http://www.yiiframework.com/" target="_blank">
        <img src="https://www.yiiframework.com/files/logo/yii.png" width="400" alt="Yii Framework" />
    </a>
</p>


## Yii2 API Boilerplate with JWT and RBAC

[![Twitter](https://img.shields.io/twitter/follow/alimmirzaee.svg?style=social&label=Follow)](https://twitter.com/intent/follow?screen_name=alimmirzaee)


Yii2 API Boilerplate is a "starter kit" you can use to build your first API in seconds. As you can easily imagine, it is built on top of the awesome Yii2 Framework.

I used these packages:

* lcobucci/jwt - [lcobucci/jwt](https://github.com/lcobucci/jwt)
* Yii2 JWT - [sizeg/yii2-jwt](https://github.com/sizeg/yii2-jwt)

## Installation

1. run `composer create-project mmirzaee/yii2-api-boilerplate-jwt project-name`
2. enjoy your coffee ☕

Once the project creation procedure completed, edit db config and run the `./yii migrate` command to create the required tables.

## Usage

1. Edit `config/params.php` and set your own JWT Key and etc. 
2. For preparing RBAC try editing `commands/RbacController` and add/edit your own `Roles` and `Permissions`.
3. run `./yii rbac/init` for initializing roles and permissions. This will also create the initial user with username: `root` and password: `ChangeThisPassw0rdTo0` (You can change these in RbacController.php)
## Main Features

### Ready-To-Use Authentication Controllers

You don't have to worry about authentication anymore. I created four controllers you can find in the `controllers/AuthController.php` for those operations.

Try these APIs:

* `POST api/login`, to do the login and get your access token;
* `POST api/refresh`, to refresh an existent access token by getting a new one;
* `POST api/signup`, to create a new user into your application;
* `GET api/auth/me`, to get current user data;

## Configuration

You can find all the boilerplate specific settings in the `config/params.php` config file.

```php
<?php

return [
    'adminEmail' => 'admin@example.com',
    'TokenEncryptionKey' => '234234rdfedcecrfcf',
    'TokenID' => 'Ssdfkm0c42c2r24crr2',
    'JwtIssuer' => 'ChangeThisToIssuer',
    'JwtAudience' => 'ChangeThisToAudience',
    'JwtExpire' => 3600,
    'DefaultSignupRole' => 'member',
];

```

### Database

Edit the file `config/db.php` with real data, for example:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '1234',
    'charset' => 'utf8',
];
```

**NOTES:**
- Yii won't create the database for you, this has to be done manually before you can access it.
- Check and edit the other files in the `config/` directory to customize your application as required.
- Refer to the README in the `tests` directory for information specific to basic application tests.


TESTING
-------

Tests are located in `tests` directory. They are developed with [Codeception PHP Testing Framework](http://codeception.com/).
By default there are 3 test suites:

- `unit`
- `functional`
- `acceptance`

Tests can be executed by running

```
vendor/bin/codecept run
```

The command above will execute unit and functional tests. Unit tests are testing the system components, while functional
tests are for testing user interaction. Acceptance tests are disabled by default as they require additional setup since
they perform testing in real browser. 


### Running  acceptance tests

To execute acceptance tests do the following:  

1. Rename `tests/acceptance.suite.yml.example` to `tests/acceptance.suite.yml` to enable suite configuration

2. Replace `codeception/base` package in `composer.json` with `codeception/codeception` to install full featured
   version of Codeception

3. Update dependencies with Composer 

    ```
    composer update  
    ```

4. Download [Selenium Server](http://www.seleniumhq.org/download/) and launch it:

    ```
    java -jar ~/selenium-server-standalone-x.xx.x.jar
    ```

    In case of using Selenium Server 3.0 with Firefox browser since v48 or Google Chrome since v53 you must download [GeckoDriver](https://github.com/mozilla/geckodriver/releases) or [ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/downloads) and launch Selenium with it:

    ```
    # for Firefox
    java -jar -Dwebdriver.gecko.driver=~/geckodriver ~/selenium-server-standalone-3.xx.x.jar
    
    # for Google Chrome
    java -jar -Dwebdriver.chrome.driver=~/chromedriver ~/selenium-server-standalone-3.xx.x.jar
    ``` 
    
    As an alternative way you can use already configured Docker container with older versions of Selenium and Firefox:
    
    ```
    docker run --net=host selenium/standalone-firefox:2.53.0
    ```

5. (Optional) Create `yii2_basic_tests` database and update it by applying migrations if you have them.

   ```
   tests/bin/yii migrate
   ```

   The database configuration can be found at `config/test_db.php`.


6. Start web server:

    ```
    tests/bin/yii serve
    ```

7. Now you can run all available tests

   ```
   # run all available tests
   vendor/bin/codecept run

   # run acceptance tests
   vendor/bin/codecept run acceptance

   # run only unit and functional tests
   vendor/bin/codecept run unit,functional
   ```

### Code coverage support

By default, code coverage is disabled in `codeception.yml` configuration file, you should uncomment needed rows to be able
to collect code coverage. You can run your tests and collect coverage with the following command:

```
#collect coverage for all tests
vendor/bin/codecept run -- --coverage-html --coverage-xml

#collect coverage only for unit tests
vendor/bin/codecept run unit -- --coverage-html --coverage-xml

#collect coverage for unit and functional tests
vendor/bin/codecept run functional,unit -- --coverage-html --coverage-xml
```

You can see code coverage output under the `tests/_output` directory.
## Feedback

I currently made this project for personal purposes. I decided to share it here to help anyone with the same needs. If you have any feedback to improve it, feel free to make a suggestion, or open a PR!