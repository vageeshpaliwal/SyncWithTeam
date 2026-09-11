<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Services\CurrentUserService;
use App\Services\UserService;

class ProfileController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService,
        protected UserService $userService,
    ) {}

    public function edit()
    {
        $currentUser = $this->currentUserService->getUser();
        $organization = $this->currentUserService->getOrganization();

        return view('profile.edit', compact('currentUser', 'organization'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $currentUser = $this->currentUserService->getUser();

        $this->userService->updateProfile($currentUser, $request->validated(), $request);

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}
