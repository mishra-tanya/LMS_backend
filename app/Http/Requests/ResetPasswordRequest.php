<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ApiResponse;

class ResetPasswordRequest extends FormRequest
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
            'token'                 => 'required|string',
            'email'                 => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed', 
            'password_confirmation' => 'required|string|min:6|same:password',
        ];
    }

    protected function failedValidation(Validator $validator){
        throw new HttpResponseException(
            ApiResponse::error('Validation errors', $validator->errors(), 422)
        );
    }
}
