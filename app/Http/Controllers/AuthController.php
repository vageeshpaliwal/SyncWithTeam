<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function welcome()
    {
       
        return view('welcome');
    }

    public function registerOrganization()
    {
        return view('auth.register-organization');
    }
    public function login()
    {
        return view('auth.login');
    }

    public function storeOrganization(RegisterRequest $request)
    {
        $validatedData = $request->validated();
        $this->authService->register($validatedData);
        return redirect()->route('login')->with('success', 'Organization registered successfully!');
    }

    public function loginPost(LoginRequest $request)
    {

        $credentials = $request->validated();
        if ($this->authService->login($credentials)) {
            return redirect()->route('dashboard')->with('success', 'Login successful!');
        } else {
            return redirect()->back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }
    }
    public function logout(Request $request)
    {
        $this->authService->logout($request);

        return redirect()
            ->route('login')
            ->with('success', 'Logged out successfully.');
    }
}


    