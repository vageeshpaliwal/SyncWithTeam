<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CurrentUserService;

class DashboardController extends Controller
{

     protected CurrentUserService $currentUserService;

    public function __construct(CurrentUserService $currentUserService)
    {
        $this->currentUserService = $currentUserService;
    }

    public function index()
    {
        $currentUser = $this->currentUserService->getUser();

      return view('dashboard.index', compact('currentUser'));
    }
}