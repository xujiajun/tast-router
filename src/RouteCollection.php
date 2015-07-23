<?php

namespace TastRouter;

/**
 * Class RouteCollection
 * @package TastRouter
 */
class RouteCollection extends \SplObjectStorage
{
    /**
     * @param Route $route
     */
    public function attachRoute(Route $route)
    {
        parent::attach($route);
    }

    /**
     * @return array
     */
    public function all()
    {
        $temp = [];
        foreach ($this as $route) {
            $temp[] = $route;
        }

        return $temp;
    }
}