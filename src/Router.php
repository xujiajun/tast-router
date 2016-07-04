<?php
namespace TastRouter;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class Router
 * @package TastRouter
 * @author xujiajun [github.com/xujiajun]
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
     * @var null
     */
    private static $parameters = null;

    /**
     * @var null
     */
    private static $middleware = null;

    private static $webPath = __BASEDIR__ . '/web/';

    /**
     * @param RouteCollection $routeCollection
     */
    public function __construct(RouteCollection $routeCollection)
    {
        $this->routes = $routeCollection;
    }

    /**
     * @return array|RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    public function setRoutes(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    public static function setParameters($parameters)
    {
        self::$parameters = $parameters;
    }

    public static function setMiddleware($eventDispatcher, $event)
    {
        self::$middleware = [$eventDispatcher, $event];
    }

    /**
     * @return mixed
     */
    public function matchCurrentRequest()
    {
        $request = Request::createFromGlobals();
        $requestMethod = $request->getMethod();
        $pathInfo = $request->getPathInfo();

        $webPath = self::$webPath;
        if (is_file($webPath . $pathInfo) && file_exists($webPath . $pathInfo)) {
            $content = file_get_contents(__BASEDIR__ . '/web/' . $pathInfo);
            echo $content;
            return;
        }

        return $this->match($pathInfo, $requestMethod);
    }

    /**
     * @param $requestUrl
     * @param string $requestMethod
     * @return mixed
     * @throws \Exception
     */
    public function match($requestUrl, $requestMethod = 'GET')
    {
        $isRegexp = false;

        $this->_bind();

        foreach ($this->routes->all() as $route) {

            if (strpos($requestUrl, $route->getNamekey(), 0)) {
                throw new \Exception("Don't use route name key as part of your route");
            }

            if (!in_array($requestMethod, (array)$route->getMethods())) {
                continue;
            }

            $url = $route->getUrl();

            if (in_array($requestUrl, (array)$url)) {
                if (self::$middleware) {
                    list($eventDispatcher, $event) = self::$middleware;
                    $route->setMiddleware($eventDispatcher, $event);
                }
                return $route->dispatch(self::$parameters);
            }

            $isRegexp = $this->_PregMatch($url, $requestUrl, $route);

            if (!in_array($requestUrl, (array)$url) && $isRegexp == false) {
                continue;
            }

            if (self::$middleware) {
                list($eventDispatcher, $event) = self::$middleware;
                $route->setMiddleware($eventDispatcher, $event);
            }
            return $route->dispatch(self::$parameters);
        }
        throw new \Exception("Error Url");
    }

    /**
     * @param $routeName
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function generate($routeName, array $parameters = [])
    {
        if (empty($this->namedroute[$routeName])) {
            throw new \Exception("No route named $routeName .");
        }

        $url = $this->namedroute[$routeName]->getUrl();
        preg_match_all('/\/{\w+}\/?/', $url, $matches);
        $matches = $matches[0];
        if (!empty($matches)) {
            $matches[count($matches) - 1] .= '/';
            return preg_replace($matches, array_reverse($parameters), $url);
        }

        return $url;
    }

    /**
     * @param array $config
     * @return Router
     * @throws \Exception
     */
    public static function parseConfig(array $config)
    {
        $collection = new RouteCollection();
        foreach ($config as $name => $routeConfig) {
            if (empty($name)) {
                throw new \Exception('Check your config file! route name is missing');
            }

            //优先考虑routeName
            if (empty($routeConfig['parameters']['routeName'])) {
                $routeConfig['parameters']['routeName'] = $name;
            }

            $collection->attachRoute(new Route($routeConfig['pattern'], $routeConfig['parameters']));
        }

        return new Router($collection);
    }

    //bind name
    private function _bind()
    {
        foreach ($this->routes->all() as $route) {
            $name = $route->getName();
            if (!empty($name)) {
                $this->namedroute[$name] = $route;
            }
        }
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