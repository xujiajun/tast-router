# TastRouter  [![License](https://poser.pugx.org/xujiajun/tast-router/license)](https://packagist.org/packages/xujiajun/tast-router) [![Code Climate](https://codeclimate.com/github/xujiajun/tast-router/badges/gpa.svg)](https://codeclimate.com/github/xujiajun/tast-router)

A Simple PHP Router

* 支持RESTful风格
* 支持反向路由
* 支持动态参数绑定
* 支持对参数正则检验

##composer方式获得

```
{
    "require":{
        "xujiajun/tast-router":"dev-master"
    }
}

```
当然也可以直接clone

## Usage

step1
```
sudo composer install
```

step2:
```
//web/index.php

require __DIR__.'/../vendor/autoload.php';
use TastRouter\Route;
use TastRouter\Router;
use TastRouter\RouteCollection;

$collection = new RouteCollection();

$collection->attachRoute(new Route('/user/do',[
    '_controller' => 'TastRouter\\App\\Controllers\\UserController::doAction',
    'methods' => 'GET',
]));

//使用正则
$collection->attachRoute(new Route('/user/{name}',[
    '_controller' => 'TastRouter\\App\\Controllers\\UserController::indexAction',
    'methods' => 'GET',
    'name'=>'\w+',
//    'id'=>'\d+',
]));



$router = new Router($collection);
$route = $router->matchCurrentRequest();

```




## License
[MIT Licensed](http://www.opensource.org/licenses/MIT)
