<?php
namespace TastRouter;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Route
 * @package TastRouter
 * @author xujiajun [github.com/xujiajun]
 */
class Route
{
    private $url;

    private $methods = ['GET', 'POST', 'PUT', 'DELETE'];

    private $config = [];

    private $parameters = [];

    private $query = [];

    private $name = null;

    private $nameKey = 'routeName';//路由名的key值

    private $pattern = '\w+';

    private $middleware = null;

    public function __construct($url, $config)
    {
        $this->url = $url;
        $this->config = $config;
        $this->methods = isset($config['methods']) ? $config['methods'] : array();
        $this->name = isset($config[$this->nameKey]) ? $config[$this->nameKey] : null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    public function __isset($name)
    {
        return isset($this->$name);
    }

    public function __unset($name)
    {
        unset($this->name);
    }

    public function getNameKey()
    {
        return $this->nameKey;
    }

    public function getDefaultPattern()
    {
        return $this->pattern;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        return $this->name = (String)$name;
    }

    public function setMiddleware($eventDispatcher, $event)
    {
        return $this->middleware = [$eventDispatcher, $event];
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function setMethods(array $methods)
    {
        $this->methods = $methods;
    }

    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function getParameterByName($name)
    {
        return $this->parameters[$name];
    }

    public function dispatch($parameters)
    {
        $action = explode('::', $this->config['_controller']);

        if (count($action) != 2) {
            throw new \Exception('delimiter is wrong. ');
        }

        list($bundleName, $module) = explode('@', $action[0]);

        $class = "TastPHP\\{$bundleName}Bundle\Controller\\{$module}Controller";
        if (!class_exists($class)) {
            throw new \Exception('Class {$action[0]} not exists.');
        }

        $request = Request::createFromGlobals();

        $request->query = new ParameterBag(array_merge($_GET, $this->parameters,$this->query));

        if (!empty($parameters)) {
            $parameters['Request'] = $request;
        }

        $instance = empty($parameters) ? new $class : new $class($parameters);

        $action = $action[1] . "Action";

        if ($this->middleware) {
            list($eventDispatcher, $event) = $this->middleware;
            $event->setParameters($parameters);
            $eventDispatcher->dispatch($event::NAME, $event);
        }

        return call_user_func(array($instance, $action), $request, $this->parameters);
    }
}