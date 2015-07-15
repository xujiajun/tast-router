<?php
namespace TastRouter;

class Route
{
    private $url;

    private $methods = ['GET', 'POST', 'PUT', 'DELETE'];

    private $config = [];

    private $parameters = [];

    public function __construct($url, $config)
    {
        $this->url = $url;
        $this->config = $config;
        $this->methods = isset($config['methods']) ? $config['methods'] : array();
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getUrl()
    {
        return $this->url;
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
        $instance = new $action[0];
        call_user_func_array(array($instance, $action[1]), $this->parameters);
    }
}