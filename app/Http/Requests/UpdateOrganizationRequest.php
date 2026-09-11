<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $organizationId = session('organization_id');

        return [

            /*
            |--------------------------------------------------------------------------
            | Organization Information
            |--------------------------------------------------------------------------
            */

            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'name' => 'required|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'required|email|max:255|unique:organizations,email,' . $organizationId,
            'slug' => 'nullable|string|max:255|unique:organizations,slug,' . $organizationId,
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'allowed_ips' => 'nullable|string',
            'geofence_latitude' => 'nullable|numeric|between:-90,90',
            'geofence_longitude' => 'nullable|numeric|between:-180,180',
            'geofence_radius_meters' => 'nullable|integer|min:10|max:50000',
            'enforce_ip_restriction' => 'nullable|boolean',
            'enforce_geofence' => 'nullable|boolean',
        ];

    }

    public function messages(): array
    {
        return [

            'name.required' => 'Organization name is required.',

            'email.required' => 'Organization email is required.',

            'email.unique' => 'Organization email already exists.',

            'logo.image' => 'Please upload a valid image.',

            'logo.max' => 'Logo size should not exceed 2 MB.',

        ];
    }
}