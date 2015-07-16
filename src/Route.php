<?php
namespace TastRouter;

class Route
{
    private $url;

    private $methods = ['GET', 'POST', 'PUT', 'DELETE'];

    private $config = [];

    private $parameters = [];

    private $name = null;

    private $nameKey = 'routeName';//路由名的key值

    private $pattern = '\w+';

    public function __construct($url, $config)
    {
        $this->url = $url;
        $this->config = $config;
        $this->methods = isset($config['methods']) ? $config['methods'] : array();
        $this->name = isset($config[$this->nameKey]) ? $config[$this->nameKey] : null;
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

    public function setConfig($key,$value)
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

    public function dispatch()
    {
        $action = explode('::', $this->config['_controller']);

        if (count($action) != 2) {
            throw new \Exception('delimiter is wrong. ');
        }

        $instance = new $action[0];
        call_user_func_array(array($instance, $action[1]), $this->parameters);
    }
}