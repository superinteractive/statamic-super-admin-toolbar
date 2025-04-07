<?php

namespace SuperInteractive\SuperAdminToolbar\Tags;

use Statamic\Tags\Tags;

class SuperAdminToolbar extends Tags
{
    public function index()
    {
        return view('super-admin-toolbar::load-toolbar')->render();
    }
}
