<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ApiResponse;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|regex:/^[\pL\s\-]+$/u', 
            'email' => 'required|email|unique:users,email|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z0-9]{2,6}$/ix',  
            'password' => 'required|string|min:6|confirmed|regex:/[A-Za-z]/|regex:/[0-9]/', 
            'password_confirmation' => 'required|string|min:6|same:password',  
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 6 characters long.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.regex' => 'The password must contain at least one letter and one number.',
        ];
    }

    /**
     * Sanitize incoming request data before validation.
     *
     * @return void
     */
    public function sanitize(): void
    {
        $this->merge([
            'name' => trim($this->input('name')),
            'email' => strtolower(trim($this->input('email'))),
        ]);
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error('Validation errors', $validator->errors(), 422));
    }

    /**
     * Override the `all()` method to sanitize input before validation.
     */
    public function all($keys = null)
    {
        $this->sanitize(); 

        return parent::all($keys); 
    }
}
