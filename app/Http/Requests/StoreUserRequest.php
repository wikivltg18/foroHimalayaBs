<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()?->can('registrar usuario') ?? false;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'foto_perfil' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg','max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'f_nacimiento' => ['nullable', 'date'],
            'id_area' => ['required', 'exists:areas,id'],
            'id_cargo' => ['required', 'exists:cargos,id'],
            'role' => ['required', 'exists:roles,id'],
        ];
    }
}