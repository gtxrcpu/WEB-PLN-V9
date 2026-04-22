<?php

namespace App\Http\Controllers;

use App\Models\KartuRumahPompa;
use App\Models\RumahPompa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RumahPompaKartuController extends Controller
{
    public function create(Request $request)
    {
        $rumahPompaId = $request->query('rumah_pompa_id');

        if (! $rumahPompaId) {
            return redirect()
                ->route('rumah-pompa.index')
                ->with('error', 'Rumah Pompa ID tidak ditemukan');
        }

        $rumahPompa = RumahPompa::findOrFail($rumahPompaId);
        $template = \App\Models\KartuTemplate::getTemplate('rumah-pompa', $rumahPompa->unit_id);

        $latestKartu = KartuRumahPompa::where('rumah_pompa_id', $rumahPompaId)
            ->orderBy('revisi', 'desc')
            ->first();

        if ($latestKartu && $latestKartu->rejected_at) {
            $nextRevisi = str_pad((int) $latestKartu->revisi, 2, '0', STR_PAD_LEFT);
        } else {
            $nextRevisi = '00';
        }

        return view('rumah-pompa.kartu.create', compact('rumahPompa', 'template', 'nextRevisi'));
    }

    public function store(Request $request)
    {
        $rumahPompa = RumahPompa::findOrFail($request->rumah_pompa_id);
        $template = \App\Models\KartuTemplate::getTemplate('rumah-pompa', $rumahPompa->unit_id);

        // Debug: Log request data
        \Log::info('Rumah Pompa Kartu Store Request', [
            'all_data' => $request->all(),
            'template_exists' => $template ? 'yes' : 'no',
            'inspection_fields_count' => $template && $template->inspection_fields ? count($template->inspection_fields) : 0,
        ]);

        // Build validation rules
        $rules = [
            'rumah_pompa_id' => ['required', 'exists:rumah_pompas,id'],
            'kesimpulan' => ['required', 'string', 'max:50'],
            'tgl_periksa' => ['required', 'date'],
            'petugas' => ['required', 'string', 'max:100'],
            'pengawas' => ['nullable', 'string', 'max:100'],
        ];

        // Add dynamic inspection fields validation
        if ($template && $template->inspection_fields) {
            foreach ($template->inspection_fields as $index => $field) {
                $fieldName = 'inspection_'.$index;
                $rules[$fieldName] = ['required', 'string', 'max:255'];
            }
        } else {
            // Fallback ke field lama
            $rules = array_merge($rules, [
                'pompa_utama' => ['required', 'string', 'max:50'],
                'pompa_cadangan' => ['required', 'string', 'max:50'],
                'jockey_pump' => ['required', 'string', 'max:50'],
                'panel_kontrol' => ['required', 'string', 'max:50'],
                'uji_fungsi' => ['required', 'string', 'max:50'],
            ]);
        }

        // Custom validation messages
        $messages = [];
        if ($template && $template->inspection_fields) {
            foreach ($template->inspection_fields as $index => $field) {
                $fieldName = 'inspection_'.$index;
                $messages[$fieldName.'.required'] = 'Field "'.$field['label'].'" wajib diisi.';
            }
        }

        $data = $request->validate($rules, $messages);
        if ($template && $template->inspection_fields) {
            $inspectionData = [];
            foreach ($template->inspection_fields as $index => $field) {
                $fieldName = 'inspection_'.$index;
                $inspectionData[] = [
                    'key' => $field['key'] ?? $fieldName,
                    'label' => $field['label'] ?? ($field['key'] ?? $fieldName),
                    'value' => $data[$fieldName] ?? null,
                ];
            }

            if (Schema::hasColumn('kartu_rumah_pompas', 'inspection_data')) {
                $data['inspection_data'] = $inspectionData;
            }

            $legacyFields = ['pompa_utama', 'pompa_cadangan', 'jockey_pump', 'panel_kontrol', 'uji_fungsi'];
            foreach ($legacyFields as $field) {
                $data[$field] = $data[$field] ?? '-';
            }

            foreach ($template->inspection_fields as $index => $field) {
                $fieldName = 'inspection_'.$index;
                if (! array_key_exists($fieldName, $data)) {
                    continue;
                }

                $fieldKey = $field['key'] ?? null;
                if (is_string($fieldKey) && in_array($fieldKey, $legacyFields, true)) {
                    $data[$fieldKey] = $data[$fieldName] ?? '-';
                }

                unset($data[$fieldName]);
            }
        } elseif (Schema::hasColumn('kartu_rumah_pompas', 'inspection_data')) {
            $data['inspection_data'] = [
                ['key' => 'pompa_utama', 'label' => 'Pompa Utama', 'value' => $data['pompa_utama'] ?? null],
                ['key' => 'pompa_cadangan', 'label' => 'Pompa Cadangan', 'value' => $data['pompa_cadangan'] ?? null],
                ['key' => 'jockey_pump', 'label' => 'Jockey Pump', 'value' => $data['jockey_pump'] ?? null],
                ['key' => 'panel_kontrol', 'label' => 'Panel Kontrol', 'value' => $data['panel_kontrol'] ?? null],
                ['key' => 'uji_fungsi', 'label' => 'Uji Fungsi', 'value' => $data['uji_fungsi'] ?? null],
            ];
        }

        // Tambahkan user_id
        $data['user_id'] = auth()->id();

        $latestKartu = KartuRumahPompa::where('rumah_pompa_id', $data['rumah_pompa_id'])
            ->orderBy('revisi', 'desc')
            ->first();

        if ($latestKartu && $latestKartu->rejected_at) {
            $data['revisi'] = str_pad((int) $latestKartu->revisi, 2, '0', STR_PAD_LEFT);
        } else {
            $data['revisi'] = '00';
        }

        // Log final data before insert
        \Log::info('Final data before insert', ['data' => $data]);

        // Simpan kartu inspeksi Rumah Pompa
        KartuRumahPompa::create($data);

        return redirect()
            ->route('rumah-pompa.index')
            ->with('success', 'Kartu Kendali Rumah Pompa berhasil disimpan dan menunggu approval');
    }
}
