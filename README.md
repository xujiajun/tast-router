# TastRouter  [![License](https://poser.pugx.org/xujiajun/tast-router/license)](https://packagist.org/packages/xujiajun/tast-router) [![Code Climate](https://codeclimate.com/github/xujiajun/tast-router/badges/gpa.svg)](https://codeclimate.com/github/xujiajun/tast-router) [![Build Status](https://travis-ci.org/xujiajun/tast-router.svg?branch=master)](https://travis-ci.org/xujiajun/tast-router)

A Simple PHP Router

* 支持RESTful风格
* 支持反向路由
* 支持动态参数绑定
* 支持对参数正则检验
* 支持Yaml格式的路由配置

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

$controller = 'TastRouter\\App\\Controllers\\UserController';

//普通用法
$collection
->attachRoute(new Route('/user/do',[
    '_controller' => "$controller::doAction",
    'methods' => 'GET',
]));

//使用正则
$collection
->attachRoute(new Route('/user/{user}',[
    '_controller' => "$controller::indexAction",
    'methods' => 'GET',
    'user'=>'\w+',
//    'id'=>'\d+',
]));

//路由名绑定
$collection
->attachRoute(new Route('/hello/{hello}',[
    '_controller' => "$controller::indexAction",
    'methods' => 'GET',
    'hello'=>'\w+',
    'routeName'=>'say_hello',//bind route name
//    'id'=>'\d+',
]));


$router = new Router($collection);
$route = $router->matchCurrentRequest();

//解析路由
echo $router->generate('say_hello',['hello'=>'xujiajun']);// 输出 /hello/xujiajun

```
以上用法太麻烦？

TastRouter也支持Yaml的配置.方便管理你的路由:

```
//web/index.php

require __DIR__.'/../vendor/autoload.php';
use TastRouter\Router;
use Symfony\Component\Yaml\Yaml;

$file = __DIR__.'/../src/Config/routes.yml';
$array = Yaml::parse(file_get_contents($file));
$router = Router::parseConfig($array);
$route = $router->matchCurrentRequest();

```

## License
[MIT Licensed](http://www.opensource.org/licenses/MIT)
