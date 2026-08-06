<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CurrentUserService;
use App\Http\Requests\InviteUserRequest;
use App\Services\UserService;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserInvitationMail;
use Illuminate\Support\Facades\Log;

class Usercontroller extends Controller
{
     protected CurrentUserService $currentUserService;
     protected UserService $userService;

    public function __construct(CurrentUserService $currentUserService, UserService $userService)
    {
        $this->currentUserService = $currentUserService;
        $this->userService = $userService;
    }
    public function users()
    {
        return view('users.index');
    }
    public function invite()
    {
        $roles = $this->currentUserService->getRoles();
         return view('users.invite', compact('roles'));
    }
    public function addUser(InviteUserRequest $request)
    {
        $validatedData = $request->validated();
        $org = $this->currentUserService->getOrganization();
        $invitedby = $this->currentUserService->getUser();


        $invitation = $this->userService->addUser($validatedData, $org->id, $invitedby->id);
        if (!$invitation) {
            return redirect()->back()->with('error', 'Failed to create invitation.');
            logger()->error('Failed to create invitation for user: ' . $validatedData['email']);
        }
        log::info('Invitation created successfully for user: ' . $validatedData['email']);
        log::error('Invitation not created for user: ' . $validatedData['email']);
        Mail::to($invitation->email)->send(new UserInvitationMail($invitation));
        return redirect()->route('users.index')->with('success', 'User added successfully.');
    }
}
