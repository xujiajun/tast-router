<?php

namespace TastRouter\App\Controllers;

class UserController
{
    public function indexAction($parameters,$parameters2='')
    {
        echo 'user index Action and  parameter:'.$parameters." parmmeter2:".$parameters2;
    }

    public function doAction()
    {
        echo 'do action';
    }
}