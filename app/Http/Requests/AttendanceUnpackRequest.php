<?php

namespace App\Http\Requests;

use App\Enums\AttendanceBoxType;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceUnpackRequest extends FormRequest
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
            'box' => ['required', 'string', AttendanceBoxType::rule()],
            'is_premium' => 'numeric|min:0|max:1',
        ];
    }
}
