# TastRouter  [![License](https://poser.pugx.org/xujiajun/tast-router/license)](https://packagist.org/packages/xujiajun/tast-router) [![Code Climate](https://codeclimate.com/github/xujiajun/tast-router/badges/gpa.svg)](https://codeclimate.com/github/xujiajun/tast-router) [![Build Status](https://travis-ci.org/xujiajun/tast-router.svg?branch=master)](https://travis-ci.org/xujiajun/tast-router) [![Test Coverage](https://codeclimate.com/github/xujiajun/tast-router/badges/coverage.svg)](https://codeclimate.com/github/xujiajun/tast-router/coverage)

A Simple PHP Router

* 支持RESTful风格
* 支持反向路由
* 支持动态参数绑定
* 支持对参数正则检验

##Requirements

  PHP5.4+

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
    '_controller' => 'UserController::doAction',
    'methods' => 'GET',
]));

//使用正则
$collection->attachRoute(new Route('/user/{name}',[
    '_controller' => 'UserController::indexAction',
    'methods' => 'GET',
    'name'=>'\w+',
    'routeName'=>'user_get',//bind route name
//    'id'=>'\d+',
]));



$router = new Router($collection);
$route = $router->matchCurrentRequest();

echo $router->generate('user_get',['user'=>'xujiajun']);// 输出 /user/xujiajun

```




## License
[MIT Licensed](http://www.opensource.org/licenses/MIT)
