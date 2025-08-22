<?php

namespace App\Modules\Home;

use App\Views\ViewRender;

class HomeController
{
    public function index()
    {
        var_dump(basename("http://localhost/upload/intervention_images/20250822074736_dfb18274c2e7fe0b.png"));
        die;
        $view = new ViewRender();

        return $view->render("home/index");
    }
}
