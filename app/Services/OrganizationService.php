<?php

namespace App\Services;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class OrganizationService
{
    public function update(
        Organization $organization,
        array $data,
        Request $request
    ): void {

        DB::transaction(function () use (
            $organization,
            $data,
            $request
        ) {

            /*
            |--------------------------------------------------------------------------
            | Upload Organization Logo
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('logo')) {

                /*
                |--------------------------------------------------------------------------
                | Delete Old Logo
                |--------------------------------------------------------------------------
                */

                if ($organization->logo && Storage::disk('public')->exists($organization->logo)) 
                {
                    Storage::disk('public')->delete($organization->logo);
                }

                /*
                |--------------------------------------------------------------------------
                | Store New Logo
                |--------------------------------------------------------------------------
                */

                $data['logo'] = $request->file('logo')->store('organizations', 'public');
            }

            /*
            |--------------------------------------------------------------------------
            | Update Slug
            |--------------------------------------------------------------------------
            */

            $data['slug'] = Str::slug($data['name']);

            /*
            |--------------------------------------------------------------------------
            | Update Organization
            |--------------------------------------------------------------------------
            */

            $organization->update($data);

        });

    }
}