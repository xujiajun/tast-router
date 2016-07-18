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

    private static $instance;

    public function __construct($url, $config)
    {
        self::$instance = $this;
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

    /**
     * build a moudle class
     *
     * @param  class
     * @return ReflectionClass class
     */
    private function buildMoudle($class)
    {
        if (!class_exists($class)) {
            throw new \Exception("Class " . $class . " not found !");
        }

        $reflector = new \ReflectionClass($class);

        return $reflector;
    }

    public function dispatch($container)
    {
        $action = explode('::', $this->config['_controller']);

        if (count($action) != 2) {
            throw new \Exception('delimiter is wrong. ');
        }

        list($bundleName, $module) = explode('@', $action[0]);

        $class = "TastPHP\\{$bundleName}Bundle\Controller\\{$module}Controller";
        $reflector = $this->buildMoudle($class);
        $action = $action[1] . "Action";
        if (!$reflector->hasMethod($action)) {
            throw new \Exception("Class " . $class . " exist ,But the Action " . $action . " not found");
        }

        $request = Request::createFromGlobals();
        if ($this->query) {
            $request->query = new ParameterBag($this->query);
        }

        if ($this->parameters) {
            $request->attributes = new ParameterBag($this->parameters);
        }

        if (!empty($container)) {
            $container['Request'] = $request;
        }

        if ($this->middleware) {
            list($eventDispatcher, $event) = $this->middleware;
            $event->setParameters($container);
            $eventDispatcher->dispatch($event::NAME, $event);
        }

        $instance = $reflector->newInstanceArgs(array($container));
        $method = $reflector->getmethod($action);
        $args = [];
        $parameters = $this->parameters;
        foreach ($method->getParameters() as $arg) {
            $paramName = $arg->getName();
            if (isset($parameters[$paramName])) $args[$paramName] = $parameters[$paramName];
            if (!empty($arg->getClass()) && $arg->getClass()->getName() == 'Symfony\Component\HttpFoundation\Request') $args[$paramName] = $request;
        }

        return $method->invokeArgs($instance, $args);
    }
}