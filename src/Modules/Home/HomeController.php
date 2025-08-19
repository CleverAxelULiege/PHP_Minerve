<?php

namespace App\Modules\Home;

use App\Views\ViewRender;

class HomeController
{
    public function index()
    {
        $view = new ViewRender();

        return $view->render("home/index");
    }
}
