<?php
namespace TastRouter;

/**
 * Class Router
 * @package TastRouter
 * @author xujiajun
 */
class Router
{
    /**
     * @var array|RouteCollection
     */
    private $routes = [];


    /**
     * @var array
     */
    private $namedroute = [];

    /**
     * @param RouteCollection $routeCollection
     */
    public function __construct(RouteCollection $routeCollection)
    {
        $this->routes = $routeCollection;
    }

    /**
     * @return mixed
     */
    public function matchCurrentRequest()
    {
        $requestMethod = (
            isset($_POST['_method'])
            && ($_method = strtoupper($_POST['_method']))
            && in_array($_method, array('PUT', 'DELETE'))
        ) ? $_method : $_SERVER['REQUEST_METHOD'];

        $requestUrl = $_SERVER['REQUEST_URI'];

        if (($pos = strpos($requestUrl, '?')) !== false) {
            $requestUrl = substr($requestUrl, 0, $pos);
        }
        return $this->match($requestUrl, $requestMethod);
    }

    /**
     * @param $requestUrl
     * @param string $requestMethod
     * @return null
     * @throws \Exception
     */
    public function match($requestUrl, $requestMethod = 'GET')
    {
        $isRegexp = false;

        foreach ($this->routes->all() as $route) {

            if (strpos($requestUrl, $route->getNamekey(), 0)) {
                throw new \Exception("Don't use route name key as part of your route");
            }

            if (!in_array($requestMethod, (array)$route->getMethods())) {
                continue;
            }

            $url = $route->getUrl();

            //bind name
            $name = $route->getName();
            if (!empty($name)) {
                $this->namedroute[$name] = $route;
            }

            if (in_array($requestUrl, (array)$url)) {
                $route->dispatch();
                return $route;
            }

            $isRegexp = $this->_PregMatch($url, $requestUrl, $route);

            if (!in_array($requestUrl, (array)$url) && $isRegexp == false) {
                continue;
            }

            $route->dispatch();
            return $route;
        }

        return null;
    }

    /**
     * @param $routeName
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function generate($routeName, array $parameters= [])
    {
        if (empty($this->namedroute[$routeName])) {
            throw new \Exception("No route named $routeName .");
        }

        $url = $this->namedroute[$routeName]->getUrl();
        preg_match_all('/\/{\w+}\/?/', $url, $matches);
        $matches = $matches[0];

        if(!empty($matches)){
            $matches[count($matches) - 1] .= '/';
            return preg_replace($matches, array_reverse($parameters), $url);
        }

        return $url;

    }

    /**
     * @param $url
     * @param $requestUrl
     * @param $route
     * @return bool
     * @throws \Exception
     */
    private function _PregMatch($url, $requestUrl, $route)
    {
        $replace = [];
        $search = [];
        $requireKeyNames = [];
        $configs = $route->getConfig();
        preg_match_all('/{(\w+)}/', $url, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $requireKey) {

                $pattern = $route->getDefaultPattern();

                if (!empty($configs[$requireKey])) {
                    $pattern = $configs[$requireKey];
                }

                $replace[] = "($pattern)";
                $search[] = '{' . $requireKey . '}';
                $requireKeyNames[] = $requireKey;
            }

            $pattern = str_replace('/', '\/', str_replace($search, $replace, $url));
            preg_match_all("/^$pattern$/", $requestUrl, $matcheParams);
            array_shift($matcheParams);

            if (empty($matcheParams) || empty($matcheParams[0])) {
                return false;
            }

            $parameters = [];
            $pos = 0;
            foreach ($matcheParams as $matcheParam) {
                if (empty($matcheParam)) {
                    throw new \Exception('check your parameter!');
                }
                $parameterName = $requireKeyNames[$pos];
                $parameters[$parameterName] = $matcheParam[0];
                $pos++;
            }

            $route->setParameters($parameters);
            return true;
        }
    }
}