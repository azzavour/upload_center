<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    // Custom validation untuk file extension
                    $extension = strtolower($value->getClientOriginalExtension());
                    $validExtensions = ['xlsx', 'xls', 'csv'];
                    
                    if (!in_array($extension, $validExtensions)) {
                        $fail('File harus berformat XLSX, XLS, atau CSV');
                    }
                    
                    // Validasi ukuran file (40MB)
                    if ($value->getSize() > 40 * 1024 * 1024) {
                        $fail('Ukuran file maksimal 40MB');
                    }
                },
            ],
            'format_id' => 'required|exists:excel_formats,id',
            'mapping_id' => 'nullable|exists:mapping_configurations,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'File wajib diupload',
            'file.file' => 'File tidak valid',
            'format_id.required' => 'Format Excel wajib dipilih',
            'format_id.exists' => 'Format Excel tidak ditemukan',
            'mapping_id.exists' => 'Mapping configuration tidak ditemukan',
        ];
    }
}
