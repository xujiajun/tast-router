<?php

namespace TastRouter\Test\controllers;

class FooController
{
    public function indexAction($param1='',$param2='')
    {
        echo "$param1--$param2";
    }
}