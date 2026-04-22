<?php

namespace App\Http\Controllers;

use App\Models\Apab;
use Illuminate\Http\Request;

class ApabKartuController extends Controller
{
    public function create(Request $request)
    {
        $apabId = $request->query('apab_id');
        
        if (!$apabId) {
            return redirect()
                ->route('apab.index')
                ->with('error', 'APAB ID tidak ditemukan');
        }

        $apab = Apab::findOrFail($apabId);
        $template = \App\Models\KartuTemplate::getTemplate('apab', $apab->unit_id);

        $latestKartu = \App\Models\KartuApab::where('apab_id', $apabId)
            ->latest('updated_at')
            ->first();

        if ($latestKartu && $latestKartu->rejected_at) {
            $nextRevisi = str_pad((int) $latestKartu->revisi, 2, '0', STR_PAD_LEFT);
        } else {
            $nextRevisi = '00';
        }

        return view('apab.kartu.create', compact('apab', 'template', 'nextRevisi'));
    }

    public function store(Request $request)
    {
        $apab = Apab::findOrFail($request->apab_id);
        $template = \App\Models\KartuTemplate::getTemplate('apab', $apab->unit_id);
        
        // Build validation rules
        $rules = [
            'apab_id'        => ['required', 'exists:apabs,id'],
            'kesimpulan'     => ['required', 'string', 'max:50'],
            'tgl_periksa'    => ['required', 'date'],
            'petugas'        => ['required', 'string', 'max:100'],
        ];
        
        // Add dynamic inspection fields validation
        if ($template && $template->inspection_fields) {
            foreach ($template->inspection_fields as $index => $field) {
                $fieldName = 'inspection_' . $index;
                $rules[$fieldName] = ['nullable', 'string', 'max:255'];
            }
        } else {
            // Fallback ke field lama
            $rules = array_merge($rules, [
                'pressure_gauge' => ['nullable', 'string', 'max:50'],
                'pin_segel'      => ['nullable', 'string', 'max:50'],
                'selang'         => ['nullable', 'string', 'max:50'],
                'klem_selang'    => ['nullable', 'string', 'max:50'],
                'handle'         => ['nullable', 'string', 'max:50'],
                'kondisi_fisik'  => ['nullable', 'string', 'max:50'],
            ]);
        }
        
        $data = $request->validate($rules, [
            'apab_id.required' => 'Data APAB tidak valid.',
            'kesimpulan.required' => 'Kesimpulan harus dipilih.',
            'tgl_periksa.required' => 'Tanggal pemeriksaan harus diisi.',
            'petugas.required' => 'Nama petugas harus diisi.',
        ]);
        
        // Jika menggunakan template, map inspection fields ke kolom database lama
        if ($template && $template->inspection_fields) {
            // Mapping label template ke kolom database
            $fieldMapping = [
                'Pressure Gauge' => 'pressure_gauge',
                'Pin & Segel' => 'pin_segel',
                'Selang' => 'selang',
                'Klem Selang' => 'klem_selang',
                'Handle' => 'handle',
                'Kondisi Fisik' => 'kondisi_fisik',
            ];
            
            // Set default values for all required fields
            $data['pressure_gauge'] = '-';
            $data['pin_segel'] = '-';
            $data['selang'] = '-';
            $data['klem_selang'] = '-';
            $data['handle'] = '-';
            $data['kondisi_fisik'] = '-';
            
            foreach ($template->inspection_fields as $index => $field) {
                $fieldName = 'inspection_' . $index;
                if (isset($data[$fieldName])) {
                    // Map ke kolom database jika ada mapping
                    if (isset($fieldMapping[$field['label']])) {
                        $dbColumn = $fieldMapping[$field['label']];
                        $data[$dbColumn] = $data[$fieldName];
                    }
                    // IMPORTANT: Remove inspection_X field from data
                    unset($data[$fieldName]);
                }
            }
        }

        $data['user_id'] = auth()->id();
        
        $latestKartu = \App\Models\KartuApab::where('apab_id', $data['apab_id'])
            ->latest('updated_at')
            ->first();

        if ($latestKartu && $latestKartu->rejected_at) {
            $data['revisi'] = str_pad((int) $latestKartu->revisi, 2, '0', STR_PAD_LEFT);
        } else {
            $data['revisi'] = '00';
        }
        \App\Models\KartuApab::create($data);

        return redirect()
            ->route('apab.index')
            ->with('success', 'Kartu Kendali APAB berhasil disimpan');
    }
}




