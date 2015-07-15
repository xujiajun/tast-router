<?php

namespace TastRouter\Test;

require __DIR__.'/../../../vendor/autoload.php';

use TastRouter\RouteCollection;
use TastRouter\Router;
use TastRouter\Route;
use PHPUnit_Framework_TestCase;

/**
 * Class RouterTest
 * @package TastRouter\Test
 */
class RouterTest extends PHPUnit_Framework_TestCase
{

    public function testDynamicFilterMatch()
    {
        $collection = new RouteCollection();

        $route1 = new Route('/hello/{name}',[
            '_controller' => 'TastRouter\\Test\\controllers\\FooController::indexAction',
            'methods' => 'GET',
            'name'=>'\w+'
        ]);

        $route2 = new Route('/foo1/{foo1}/foo2/{foo2}',[
            '_controller' => 'TastRouter\\Test\\controllers\\FooController::indexAction',
            'methods' => 'GET',
            'foo1'=>'\w+',
            'foo2'=>'\d+'
        ]);

//        $collection->attachRoute($route1);
        $collection->attachRoute($route2);

        $router = new Router($collection);
//        $route = $router->match('/hello/xujiajun','GET');

//        $this->assertEquals($route->getParameterByName('name'),'xujiajun');
        $router->match('/foo1/foo1/foo2/123','GET');
        $this->assertEquals($route2->getParameterByName('foo1'),'foo1');
        $this->assertEquals($route2->getParameterByName('foo2'),'123');
//        $router->match('/foo1/foo1/foo2/fo2','GET');
    }
}

