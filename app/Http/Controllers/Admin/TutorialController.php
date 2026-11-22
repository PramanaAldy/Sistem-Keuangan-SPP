<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class TutorialController extends Controller
{
    public function index()
    {
        return view('admin.tutorial.index');
    }
}
