<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CurrentUserService;
use App\Services\OrganizationService;
use App\Http\Requests\UpdateOrganizationRequest;

class OrganizationController extends Controller
{
      protected CurrentUserService $currentUserService;
      protected OrganizationService $organizationService;

      public function __construct(CurrentUserService $currentUserService, OrganizationService $organizationService)
      {
            $this->currentUserService = $currentUserService;
            $this->organizationService = $organizationService;
      }
        public function settings()
        {
            $currentUser = $this->currentUserService->getUser();

            $organization = $currentUser->organization;

            return view('settings.index', compact(
                'currentUser',
                'organization'
            ));
        }

    public function edit()
    {
        $currentUser = $this->currentUserService->getUser();
        $organization = $this->currentUserService->getOrganization();
        
        return view('settings.edit', compact('currentUser', 'organization'));
    }

    public function update(UpdateOrganizationRequest $request)
    {
        // $currentUser = $this->currentUserService->getUser();
        $organization = $this->currentUserService->getOrganization();
        $this->organizationService->update($organization, $request->validated(),  $request);
        return redirect()->route('settings.index')->with('success', 'Organization updated successfully.');
    }
}
