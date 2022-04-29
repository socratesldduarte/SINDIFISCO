<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cpf'           => 'required|max:11',
            'birthday'      => 'required|date|date_format:d/m/Y',
        ];
    }

    public function messages()
    {
        return [
            'required'      => 'O campo :attribute deve ser preenchido',
            'min'           => 'O campo :attribute deve ser preenchido com pelo menos :min caracteres',
            'max'           => 'O campo :attribute deve ser preenchido com no máximo :max caracteres',
            'email'         => 'O campo :attribute deve ser um e-mail válido',
            'date'          => 'Data inválida',
            'date_format'         => 'Data deve ser no formato DD/MM/YYYY',
        ];
    }
}
