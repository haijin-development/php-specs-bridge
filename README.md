# Haijin Specs bridge to PHP frameworks

Integrates haijin/specs into web application.

[![Latest Stable Version](https://poser.pugx.org/haijin/specs-bridge/version)](https://packagist.org/packages/haijin/specs-bridge)
[![Build Status](https://travis-ci.org/haijin-development/php-specs-bridge.svg?branch=master)](https://travis-ci.org/haijin-development/php-specs-symfony-bridge)
[![License](https://poser.pugx.org/haijin/specs-bridge/license)](https://packagist.org/packages/haijin/specs-bridge)

### Version 0.1.0

If you like it a lot you may contribute by [financing](https://github.com/haijin-development/support-haijin-development) its development.

## Table of contents

1. [Installation](#c-1)
2. [Usage](#c-2)
3. [Running this project tests](#c-3)

<a name="c-1"></a>
## Installation

In the project folder run the command line:

```
composer require --dev haijin/specs haijin/specs-bridge
```

Or in the project `composer.json` file include the following:

```json
{
    ...

    "require-dev": {
        ...
        "haijin/specs": "^2.0",
        "haijin/specs-bridge": "^0.1",
        ...
    },

    ...
}
```

<a name="c-2"></a>
## Usage

### Symfony >= 3.3

Open a command line console, go to the application directory and initialize the specs folder with:

```
php ./vendor/bin/specs init
```

In the file `tests/specsBoot.php` include the following line:

```
//tests/specsBoot.php

use Haijin\Bridge\SpecsInSymfony;

SpecsInSymfony::addTo($specs);

$specs->beforeEach(function (){
    $this->resetKernel();
});
```

And that's it.

Now in any spec it is possible to declare expectation on requests with the following protocol:

```
$this->it('gets the home page contents', function (){
    $this->request('GET', '/', [], [], []);

    $this->expect( $this->getResponseStatusCode() ) ->to() ->equal(200);

    $this->expect( $this->getResponseContent() ) ->to() ->equal('<html>...</html>');

    $this->expect( $this->getJsonResponse() ) ->to() ->equal([
        'data' => [...]
    ]);
});
```

For API endpoints exist the convenience methods `$this->getJsonResponse()` and `->exactlyLike()`.

There are two differences between `->exactlyLike()` and `->equal()`.
The first one is that `->equal()` fails if the order of the fields at any level does not match the order
of the actual value. `->exactlyLike()` does not expect a particular order of the fields.

The second one is that `->exactlyLike()` allows to declare custom expectations using a closure instead of always
comparing for equality against a constant value. This is really handy to declare expectations on strings matching regular
expressions or non deterministic values like timestamps.

```
$this->it('gets the home page contents', function (){
    $this->request('GET', '/', [], [], []);

    $this->expect( $this->getJsonResponse() ) ->to() ->be() ->exactlyLike([
        'success' => true,
        'data' => [
            'user' => [
                'name' => 'Lisa',
                'lastname' => 'Simpson',
                'address' => function($value){
                     $this->expect($value) ->to() ->match('/(\w|\s)+ \d+/');
                 },
            ]
        ].
    ]);
});
```


Also, within a spec, the following objects are accessible:

```
$this->it('get the home page contents', function (){

    $domCrawler = $this->request('GET', '/', [], [], []);
    
    $this->httpClient;
    $this->crawler;
    
    $this->getHttpResponse();
    $this->getResponseStatusCode();
    $this->getResponseContent();
    $this->getJsonResponse();
    
    $anotherHttpClient = $this->createHttpClient();

    $this->kernel;
    $this->container;

});
```


<a name="c-3"></a>
## Running this project tests

Before running this the tests, setup the tests projects with

```
cd tests/symfony4/
composer install

cd ../../tests/symfony3/
composer install
```

To run the tests of this project do:

```
cd tests/symfony4/
composer specs

cd ../../tests/symfony3/
composer specs
```

Or if you want to run the tests using a Docker image with PHP 7.2:

```
sudo docker run -ti -v $(pwd):/home/dev --rm --name specs-symfony-bridge haijin/php-dev:7.2 bash

cd /home/dev/tests/symfony4/
composer install
composer specs

cd /home/dev/tests/symfony3/
composer install
composer specs
```