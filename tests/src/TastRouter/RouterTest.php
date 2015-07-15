<?php

namespace TastRouter\Test;

require __DIR__ . '/../../../vendor/autoload.php';

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
        list($route1, $route2, $route3) = $this->getRoutes();
//        $collection->attachRoute($route1);
        $collection->attachRoute($route2);
        $collection->attachRoute($route3);

        $router = new Router($collection);
//        $route = $router->match('/hello/xujiajun','GET');

//        $this->assertEquals($route->getParameterByName('name'),'xujiajun');
        $router->match('/foo1/foo1/foo2/123', 'GET');
        $this->assertInstanceOf('TastRouter\Route', $route2);
        $this->assertEquals($route2->getParameterByName('foo1'), 'foo1');
        $this->assertEquals($route2->getParameterByName('foo2'), '123');
//        $router->match('/foo1/foo1/foo2/fo2','GET');
    }

    public function testxactlyMatch()
    {
        $collection = new RouteCollection();
        list($route1, $route2, $route3) = $this->getRoutes();
        $collection->attachRoute($route3);
        $router = new Router($collection);
        $router->match('/foo1', 'GET');
    }

    /**
     * @expectedException     Exception
     */
    public function testMissMatchParams()
    {
        $collection = new RouteCollection();
        list($route1, $route2) = $this->getRoutes();
        $collection->attachRoute($route2);

        $router = new Router($collection);
        $router->match('/foo1/foo1/foo2/foo2', 'GET');
    }

    /**
     * @expectedException     Exception
     */
    public function testMissMatchParam()
    {
        $collection = new RouteCollection();
        list($route1, $route2) = $this->getRoutes();
        $collection->attachRoute($route1);

        $router = new Router($collection);
        $router->match('/hello/###', 'GET');
    }

    public function testMethod()
    {
        $collection = new RouteCollection();
        list($route1, $route2, $route3) = $this->getRoutes();
        $collection->attachRoute($route3);
        $router = new Router($collection);
        $result = $router->match('/hello/###', 'POST');
        $this->assertNull($result);
    }

    public function testMethod2()
    {
        $collection = new RouteCollection();
        list($route1, $route2, $route3) = $this->getRoutes();
        $collection->attachRoute($route3);
        $router = new Router($collection);
        $_SERVER['REQUEST_URI'] = '/foo1?name=xx';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $result = $router->matchCurrentRequest();

//        var_dump($result);
        $this->assertEquals($route3->getUrl(),'/foo1');
        $this->assertNull($result);
    }

    private function getRoutes()
    {
        $route1 = new Route('/hello/{name}', [
            '_controller' => 'TastRouter\\Test\\controllers\\FooController::indexAction',
            'methods' => 'GET',
            'name' => '\w+'
        ]);

        $route2 = new Route('/foo1/{foo1}/foo2/{foo2}', [
            '_controller' => 'TastRouter\\Test\\controllers\\FooController::indexAction',
            'methods' => 'GET',
            'foo1' => '\w+',
            'foo2' => '\d+'
        ]);

        $route3 = new Route('/foo1', [
            '_controller' => 'TastRouter\\Test\\controllers\\FooController::indexAction',
            'methods' => 'GET'
        ]);
        return [$route1, $route2, $route3];
    }

}

