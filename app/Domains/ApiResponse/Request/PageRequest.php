<?php

namespace App\Domains\ApiResponse\Request;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateProfileRequest.
 */
class PageRequest extends FormRequest
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
    $rules = [
      'title' => 'required|string|max:800',
      'content' => 'required|string',
      'excerpt' => 'nullable|string|max:800',
      'status' => 'required|string|max:191',
      'schedule_time' => 'nullable|date_format:d/m/Y',
      'status' => 'nullable|string|max:155',
      'image' => 'nullable|max:800|mimes:jpeg,jpg,png,gif,webp'
    ];

    if ($this->method() == 'PATCH') {
      $rules['slug']    = 'required|string|max:800|unique:pages,slug,' . $this->page;
    } else {
      $rules['slug']    = 'required|string|max:800|unique:pages,slug';
    }


    return $rules;
  }
}
