<?php

namespace Haijin\Bridge;

use Symfony\Contracts\Service\ResetInterface;

class SpecsInSymfony
{
    static public function addTo($specs)
    {
        $_SERVER['APP_ENV'] = 'test';

        /**
         * @return string The Kernel class name
         *
         * @throws \RuntimeException
         * @throws \LogicException
         */
        $specs->let('kernelClass', function (){
            if (!isset($_SERVER['KERNEL_CLASS']) && !isset($_ENV['KERNEL_CLASS'])) {
                if(class_exists ( 'App\Kernel')) {
                    // For Symfony 4.x
                    $_SERVER['KERNEL_CLASS'] = 'App\Kernel';
                } elseif(class_exists ( 'AppKernel')) {
                    // For Symfony 3.x
                    $_SERVER['KERNEL_CLASS'] = 'AppKernel';
                }
            }

            $class = $_ENV['KERNEL_CLASS'] ?? $_SERVER['KERNEL_CLASS'];

            if (!class_exists($class)) {
                throw new \RuntimeException(sprintf('Class "%s" doesn\'t exist or cannot be autoloaded. Check that the KERNEL_CLASS value in phpunit.xml matches the fully-qualified class name of your Kernel or override the \$specs->let("kernel", function() {}); definition.', $class, $class));
            }

            return $class;
        });

        /**
         * Creates a Kernel.
         *
         * Available options:
         *
         *  * environment
         *  * debug
         *
         * @return KernelInterface A KernelInterface instance
         */
        $specs->def('createKernel', function (array $options = []){
            if (isset($options['environment'])) {
                $env = $options['environment'];
            } elseif (isset($_ENV['APP_ENV'])) {
                $env = $_ENV['APP_ENV'];
            } elseif (isset($_SERVER['APP_ENV'])) {
                $env = $_SERVER['APP_ENV'];
            } else {
                $env = 'test';
            }

            if (isset($options['debug'])) {
                $debug = $options['debug'];
            } elseif (isset($_ENV['APP_DEBUG'])) {
                $debug = $_ENV['APP_DEBUG'];
            } elseif (isset($_SERVER['APP_DEBUG'])) {
                $debug = $_SERVER['APP_DEBUG'];
            } else {
                $debug = true;
            }

            return new $this->kernelClass($env, $debug);
        });

        /**
         * Boots the Kernel for this test.
         *
         * @return KernelInterface A KernelInterface instance
         */
        $specs->def('bootKernel', function(array $options = []){
            $this->kernel = $this->createKernel($options);
            $this->kernel->boot();

            return $this->kernel;
        });

        /**
         * Shuts the kernel down.
         */
        $specs->def('resetKernel', function (){
            if (!isset($this->kernel)) {
                return;
            }

            $container = $this->container;

            $this->kernel->shutdown();

            if ($container instanceof ResetInterface) {
                $container->reset();
            }

            unset($this->kernel);
            unset($this->container);
            unset($this->httpClient);
        });

        /**
         * Returns the kernel.
         * If the spec is calling this method is because $this->kernel has not been initialized yet,
         * therefore create and boot a new Kernel.
         */
        $specs->let('kernel', function(){
            return $this->bootKernel();
        });

        $specs->let('container', function(array $options = []){
            return $this->kernel->getContainer();
        });

        // Accessing HTTP client

        $specs->def('createHttpClient', function (array $options = [], array $server = []) {
            if(!$this->container->has('test.client')) {
                throw new \RuntimeException("The service 'test.client' is missing, possibly because the dependency 'symfony/browser-kit' is missing in the 'composer.json' file. Add it with 'composer require --dev symfony/browser-kit'.");
            }

            $client = $this->container->get('test.client');
            $client->setServerParameters($server);

            return $client;
        });

        $specs->let('httpClient', function (array $options = [], array $server = []) {
            return $this->createHttpClient();
        });

        // Making requests

        $specs->def('request', function ($method, $uri, $body=[], $files=[], $headers=[]) {
            $this->crawler = $this->httpClient->request(
                $method, $uri, $body, $files, $headers
            );

            return $this->crawler;
        });

        // Accessing responses

        $specs->def('getHttpResponse', function () {
            return $this->httpClient->getResponse();
        });

        $specs->def('getResponseStatusCode', function () {
            return $this->getHttpResponse()->getStatusCode();
        });

        $specs->def('getJsonResponse', function () {
            return json_decode($this->getResponseContent(), true);
        });

        $specs->def('getResponseContent', function () {
            return $this->getHttpResponse()->getContent();
        });
    }
}