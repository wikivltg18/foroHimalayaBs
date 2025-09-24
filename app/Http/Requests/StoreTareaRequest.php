<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mews\Purifier\Facades\Purifier;

class StoreTareaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Puedes usar policies si lo deseas, por ahora se permite a todos
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo'            => ['required', 'string', 'max:255'],
            'estado_id'         => ['required', Rule::exists('estado_tarea', 'id')],
            'area_id'           => ['required', Rule::exists('areas', 'id')],
            'usuario_id'        => ['required', Rule::exists('users', 'id')],
            'descripcion'       => ['required', 'string'],
            'tiempo_estimado_h' => ['required', 'numeric', 'min:0'],
            'fecha_de_entrega'  => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        // Sanitiza el contenido HTML de la descripción (ideal para Quill o editores similares)
        $data['descripcion'] = Purifier::clean($data['descripcion'], [
            'HTML.Allowed' => 'p,b,strong,i,em,u,ul,ol,li,a[href],br,span[style],h1,h2,h3',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty' => true,
        ]);

        return $data;
    }
}