<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function __construct()
    {
        // Check session in constructor
        if (!session()->has('user')) {
            header('Location: ' . base_url('/login'));
            exit;
        }
    }

    public function dashboard()
    {
        return view('dashboard');
    }
}