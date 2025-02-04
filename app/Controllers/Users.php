<?php

namespace App\Controllers;

class Users extends BaseController
{
    public function index(): string
    {
        return view('register');
    }
}
