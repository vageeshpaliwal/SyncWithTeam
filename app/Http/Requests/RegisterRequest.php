<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

  
        return [

            /*
            |--------------------------------------------------------------------------
            | Organization Information
            |--------------------------------------------------------------------------
            */

            'name' => 'required|string|max:255',

            'website' => 'nullable|url|max:255',

            'email' => 'required|email|max:255|unique:organizations,email',

            'phone' => 'nullable|string|max:20',


            /*
            |--------------------------------------------------------------------------
            | Owner Information
            |--------------------------------------------------------------------------
            */

            'first_name' => 'required|string|max:100',

            'last_name' => 'nullable|string|max:100',

            'username' => 'required|string|max:255|unique:users,username',

            'owner_email' => 'required|email|max:255|unique:users,email',

            'owner_phone' => 'nullable|string|max:20',
         
            /*
            |--------------------------------------------------------------------------
            | Security
            |--------------------------------------------------------------------------
            */

            'password' => 'required|min:8|confirmed',

        ];
    }

    public function messages(): array
    {
        return [

            'name.required' => 'Organization name is required.',

            'email.required' => 'Organization email is required.',

            'email.unique' => 'Organization email already exists.',

            'owner_email.required' => 'Owner email is required.',
            
            'username.required' => 'Username is required.',

            'username.unique' => 'Username already exists.',

            'owner_email.unique' => 'Owner email already exists.',

            'password.required' => 'Password is required.',

            'password.min' => 'Password must be at least 8 characters.',

            'password.confirmed' => 'Passwords do not match.',

        ];
    }
}
