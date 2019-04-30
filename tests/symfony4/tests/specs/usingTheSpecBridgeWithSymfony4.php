<?php

use Haijin\Bridge\SpecsInSymfony;

$spec->describe( 'When using haijin/specs in a Symfony 4 application', function() {

    $this->describe('to make http resquests', function (){
        $this->it( 'access the http client', function() {

            $this->expect( $this->httpClient ) ->to()
                ->be() ->a(\Symfony\Bundle\FrameworkBundle\Client::class);

        });

        $this->it( 'makes a request and access the DOM crawler', function() {

            $page = $this->request('GET', '/');

            $this->expect( $page ) ->to()
                ->be() ->a(\Symfony\Component\DomCrawler\Crawler::class);

            $this->expect( $this->crawler ) ->to()
                ->be() ->a(\Symfony\Component\DomCrawler\Crawler::class);

        });

        $this->it( 'access the response', function() {

            $this->request('GET', '/');

            $this->expect( $this->getHttpResponse() ) ->to()
                ->be() ->a(\Symfony\Component\HttpFoundation\Response::class);

        });

        $this->it( 'access the response status code of the last request', function() {

            $this->request('GET', '/');

            $this->expect( $this->getResponseStatusCode() ) ->to()
                ->equal(404);

        });

        $this->it( 'access the response content', function() {

            $this->request('GET', '/');

            $this->expect( $this->getResponseContent() ) ->to()
                ->be() ->string();

        });

        $this->it( 'access the response content as a json array', function() {

            $this->request('GET', '/');

            $this->expect( $this->getJsonResponse() ) ->to()
                ->be() ->null();

        });

        $this->it( 'creates a different http client instance', function() {

            $this->expect( $this->httpClient ) ->to()
                ->be() ->a(\Symfony\Bundle\FrameworkBundle\Client::class);

            $client = $this->createHttpClient();

            $this->expect( $client ) ->to()
                ->be() ->a(\Symfony\Bundle\FrameworkBundle\Client::class);

            $this->expect( $client ) ->to() ->be('!==') ->than($this->httpClient);
        });
    });

    $this->describe('to access the app and its container', function() {

        $this->it( 'access the response', function() {

            $this->expect( $this->kernel ) ->to()
                ->be() ->a(\App\Kernel::class);

        });

        $this->it( 'access the container', function() {

            $this->expect( $this->container ) ->to()
                ->be() ->a(\ContainerCLcoVgt\srcApp_KernelTestDebugContainer::class);

        });

    });

});