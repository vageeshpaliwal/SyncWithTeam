<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Usercontroller extends Controller
{
    public function users()
    {
        return view('users.index');
    }
    public function invite()
    {
        return view('users.invite');
    }
}
