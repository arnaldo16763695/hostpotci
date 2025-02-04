<?php

namespace App\Controllers;

class HostpotController extends BaseController
{
    protected $helpers = ['form'];
    public function index(): string
    {
        return view('hostpot'); 
    }
}
