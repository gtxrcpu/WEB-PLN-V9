<?php

namespace App\Http\Controllers;

use App\Models\BoxHydrant;
use App\Models\KartuBoxHydrant;
use Illuminate\Http\Request;

class BoxHydrantKartuController extends Controller
{
    public function create(Request $request)
    {
        $boxHydrantId = $request->query('box_hydrant_id');
        
        if (!$boxHydrantId) {
            return redirect()
                ->route('box-hydrant.index')
                ->with('error', 'Box Hydrant ID tidak ditemukan');
        }

        $boxHydrant = BoxHydrant::findOrFail($boxHydrantId);
        $template = \App\Models\KartuTemplate::getTemplate('box-hydrant', $boxHydrant->unit_id);

        $latestKartu = KartuBoxHydrant::where('box_hydrant_id', $boxHydrantId)
            ->orderBy('revisi', 'desc')
            ->first();

        if ($latestKartu && $latestKartu->rejected_at) {
            $nextRevisi = str_pad((int) $latestKartu->revisi, 2, '0', STR_PAD_LEFT);
        } else {
            $nextRevisi = '00';
        }

        return view('box-hydrant.kartu.create', compact('boxHydrant', 'template', 'nextRevisi'));
    }

    public function store(Request $request)
    {
        $boxHydrant = BoxHydrant::findOrFail($request->box_hydrant_id);
        $template = \App\Models\KartuTemplate::getTemplate('box-hydrant', $boxHydrant->unit_id);
        
        // Debug: Log request data
        \Log::info('Box Hydrant Kartu Store Request', [
            'all_data' => $request->all(),
            'template_exists' => $template ? 'yes' : 'no',
            'inspection_fields_count' => $template && $template->inspection_fields ? count($template->inspection_fields) : 0
        ]);
        
        // Build validation rules
        $rules = [
            'box_hydrant_id' => ['required', 'exists:box_hydrants,id'],
            'kesimpulan'     => ['required', 'string', 'max:50'],
            'tgl_periksa'    => ['required', 'date'],
            'petugas'        => ['required', 'string', 'max:100'],
            'pengawas'       => ['nullable', 'string', 'max:100'],
        ];
        
        // Add dynamic inspection fields validation
        if ($template && $template->inspection_fields) {
            foreach ($template->inspection_fields as $index => $field) {
                $fieldName = 'inspection_' . $index;
                $rules[$fieldName] = ['required', 'string', 'max:255'];
            }
        } else {
            // Fallback ke field lama
            $rules = array_merge($rules, [
                'pilar_hydrant'  => ['required', 'string', 'max:50'],
                'box_hydrant'    => ['required', 'string', 'max:50'],
                'nozzle'         => ['required', 'string', 'max:50'],
                'selang'         => ['required', 'string', 'max:50'],
                'uji_fungsi'     => ['required', 'string', 'max:50'],
            ]);
        }
        
        // Custom validation messages
        $messages = [];
        if ($template && $template->inspection_fields) {
            foreach ($template->inspection_fields as $index => $field) {
                $fieldName = 'inspection_' . $index;
                $messages[$fieldName . '.required'] = 'Field "' . $field['label'] . '" wajib diisi.';
            }
        }
        
        $data = $request->validate($rules, $messages);
        
        // Jika menggunakan template, map inspection fields ke kolom database lama
        if ($template && $template->inspection_fields) {
            // Mapping KEY template ke kolom database
            $fieldMapping = [
                'pilar_hydrant' => 'pilar_hydrant',
                'box_hydrant' => 'box_hydrant',
                'nozzle' => 'nozzle',
                'selang' => 'selang',
                'uji_fungsi' => 'uji_fungsi',
            ];
            
            // Initialize all required fields with default value
            $requiredFields = ['pilar_hydrant', 'box_hydrant', 'nozzle', 'selang', 'uji_fungsi'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    $data[$field] = '-';
                }
            }
            
            // Log untuk debugging
            \Log::info('Mapping inspection fields', [
                'template_fields' => $template->inspection_fields,
                'data_before_mapping' => $data
            ]);
            
            foreach ($template->inspection_fields as $index => $field) {
                $fieldName = 'inspection_' . $index;
                if (isset($data[$fieldName])) {
                    // Map ke kolom database menggunakan KEY
                    $fieldKey = $field['key'] ?? null;
                    if ($fieldKey && isset($fieldMapping[$fieldKey])) {
                        $dbColumn = $fieldMapping[$fieldKey];
                        $data[$dbColumn] = $data[$fieldName];
                        \Log::info("Mapped {$fieldName} (key: {$fieldKey}, label: {$field['label']}) to {$dbColumn} = {$data[$fieldName]}");
                    } else {
                        \Log::warning("No mapping found for field key: {$fieldKey}, label: {$field['label']}");
                    }
                    unset($data[$fieldName]);
                }
            }
            
            \Log::info('Data after mapping', ['data' => $data]);
        }

        // Tambahkan user_id
        $data['user_id'] = auth()->id();

        $latestKartu = KartuBoxHydrant::where('box_hydrant_id', $data['box_hydrant_id'])
            ->orderBy('revisi', 'desc')
            ->first();

        if ($latestKartu && $latestKartu->rejected_at) {
            $data['revisi'] = str_pad((int) $latestKartu->revisi, 2, '0', STR_PAD_LEFT);
        } else {
            $data['revisi'] = '00';
        }
        
        // Log final data before insert
        \Log::info('Final data before insert', ['data' => $data]);
        
        // Simpan kartu inspeksi Box Hydrant
        KartuBoxHydrant::create($data);

        return redirect()
            ->route('box-hydrant.index')
            ->with('success', 'Kartu Kendali Box Hydrant berhasil disimpan dan menunggu approval');
    }
}

