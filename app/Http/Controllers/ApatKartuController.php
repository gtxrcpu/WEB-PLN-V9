<?php

namespace App\Http\Controllers;

use App\Models\Apat;
use App\Models\KartuApat;
use Illuminate\Http\Request;

class ApatKartuController extends Controller
{
    /**
     * Tampilkan form Kartu Kendali APAT (create).
     * URL: /apat/kartu/create?apat_id=...
     */
    public function create(Request $request)
    {
        $apatId = $request->query('apat_id');

        $apat = Apat::findOrFail($apatId);
        
        // Get template for APAT module
        $apat = Apat::findOrFail($apatId);
        $template = \App\Models\KartuTemplate::getTemplate('apat', $apat->unit_id);

        $latestKartu = KartuApat::where('apat_id', $apatId)
            ->latest('updated_at')
            ->first();

        if ($latestKartu && $latestKartu->rejected_at) {
            $nextRevisi = str_pad((int) $latestKartu->revisi, 2, '0', STR_PAD_LEFT);
        } else {
            $nextRevisi = '00';
        }

        return view('apat.kartu.create', [
            'apat' => $apat,
            'template' => $template,
            'nextRevisi' => $nextRevisi,
        ]);
    }

    /**
     * Simpan Kartu Kendali APAT.
     */
    public function store(Request $request)
    {
        $apat = Apat::findOrFail($request->apat_id);
        $template = \App\Models\KartuTemplate::getTemplate('apat', $apat->unit_id);
        
        // Build validation rules
        $rules = [
            'apat_id'       => ['required', 'exists:apats,id'],
            'kesimpulan'    => ['required', 'string', 'max:50'],
            'tgl_periksa'   => ['required', 'date'],
            'petugas'       => ['required', 'string', 'max:100'],
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
                'kondisi_fisik' => ['nullable', 'string', 'max:50'],
                'drum'          => ['nullable', 'string', 'max:50'],
                'aduk_pasir'    => ['nullable', 'string', 'max:50'],
                'sekop'         => ['nullable', 'string', 'max:50'],
                'fire_blanket'  => ['nullable', 'string', 'max:50'],
                'ember'         => ['nullable', 'string', 'max:50'],
            ]);
        }
        
        $data = $request->validate($rules, [
            'apat_id.required' => 'Data APAT tidak valid.',
            'kesimpulan.required' => 'Kesimpulan harus dipilih.',
            'tgl_periksa.required' => 'Tanggal pemeriksaan harus diisi.',
            'petugas.required' => 'Nama petugas harus diisi.',
        ]);
        
        // Jika menggunakan template, map inspection fields ke kolom database lama
        if ($template && $template->inspection_fields) {
            // Mapping label template ke kolom database
            $fieldMapping = [
                'Kondisi Fisik' => 'kondisi_fisik',
                'Drum' => 'drum',
                'Aduk Pasir' => 'aduk_pasir',
                'Sekop' => 'sekop',
                'Fire Blanket' => 'fire_blanket',
                'Ember' => 'ember',
            ];
            
            foreach ($template->inspection_fields as $index => $field) {
                $fieldName = 'inspection_' . $index;
                if (isset($data[$fieldName])) {
                    // Map ke kolom database jika ada mapping
                    if (isset($fieldMapping[$field['label']])) {
                        $dbColumn = $fieldMapping[$field['label']];
                        $data[$dbColumn] = $data[$fieldName];
                    }
                    unset($data[$fieldName]);
                }
            }
        }

        // Tambahkan user_id
        $data['user_id'] = auth()->id();

        $latestKartu = KartuApat::where('apat_id', $data['apat_id'])
            ->latest('updated_at')
            ->first();

        if ($latestKartu && $latestKartu->rejected_at) {
            $data['revisi'] = str_pad((int) $latestKartu->revisi, 2, '0', STR_PAD_LEFT);
        } else {
            $data['revisi'] = '00';
        }

        KartuApat::create($data);

        return redirect()
            ->route('apat.index')
            ->with('success', 'Kartu Kendali APAT berhasil disimpan.');
    }
}




