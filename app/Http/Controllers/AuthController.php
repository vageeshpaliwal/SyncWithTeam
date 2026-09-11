<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SetPasswordRequest;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected UserService $userService,
    ) {}
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

    public function activateInvitation(string $token)
    {
        try {
            $invitation = $this->userService->findValidInvitation($token);
        } catch (ValidationException $e) {
            return redirect()
                ->route('login')
                ->withErrors($e->errors());
        }

        return view('auth.set-password', compact('invitation'));
    }

    public function storePassword(SetPasswordRequest $request, string $token)
    {
        try {
            $this->userService->activateInvitation($token, $request->validated('password'));
        } catch (ValidationException $e) {
            return redirect()
                ->route('login')
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('login')
            ->with('success', 'Account activated successfully. You can now log in.');
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


    