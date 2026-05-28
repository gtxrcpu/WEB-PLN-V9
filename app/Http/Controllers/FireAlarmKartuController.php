<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FiltersByUnit;
use App\Http\Controllers\Traits\AuthorizesEquipmentAccess;
use App\Http\Controllers\Traits\HasRevisionLogic;
use App\Models\FireAlarm;
use App\Models\KartuFireAlarm;
use Illuminate\Http\Request;

class FireAlarmKartuController extends Controller
{
    use FiltersByUnit, AuthorizesEquipmentAccess, HasRevisionLogic;

    /**
     * Tampilkan form Kartu Kendali Fire Alarm
     */
    public function create(Request $request)
    {
        $fireAlarmId = $request->query('fire_alarm_id');
        
        if (!$fireAlarmId) {
            return redirect()
                ->route('fire-alarm.index')
                ->with('error', 'Fire Alarm ID tidak ditemukan');
        }

        $fireAlarm = FireAlarm::findOrFail($fireAlarmId);

        // Verify user has access to this Fire Alarm's unit
        $this->authorizeEquipmentUnit($fireAlarm, 'Fire Alarm');
                $template = \App\Models\KartuTemplate::getTemplate('fire-alarm', $fireAlarm->unit_id);

        $latestKartu = KartuFireAlarm::where('fire_alarm_id', $fireAlarmId)
            ->orderBy('revisi', 'desc')
            ->first();

        if ($latestKartu && $latestKartu->rejected_at) {
            $nextRevisi = str_pad((int) $latestKartu->revisi, 2, '0', STR_PAD_LEFT);
        } else {
            $nextRevisi = '00';
        }

        return view('fire-alarm.kartu.create', compact('fireAlarm', 'template', 'nextRevisi'));
    }

    /**
     * Simpan Kartu Kendali Fire Alarm
     */
    public function store(Request $request)
    {
        $fireAlarm = FireAlarm::findOrFail($request->fire_alarm_id);

        // Verify user has access to this Fire Alarm's unit
        $this->authorizeEquipmentUnit($fireAlarm, 'Fire Alarm');

        $template = \App\Models\KartuTemplate::getTemplate('fire-alarm', $fireAlarm->unit_id);
        
        // Debug: Log request data
        \Log::info('Fire Alarm Kartu Store Request', [
            'all_data' => $request->all(),
            'template_exists' => $template ? 'yes' : 'no',
            'inspection_fields_count' => $template && $template->inspection_fields ? count($template->inspection_fields) : 0
        ]);
        
        // Build validation rules
        $rules = [
            'fire_alarm_id'  => ['required', 'exists:fire_alarms,id'],
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
                'panel_kontrol'      => ['required', 'string', 'max:50'],
                'detector'           => ['required', 'string', 'max:50'],
                'manual_call_point'  => ['required', 'string', 'max:50'],
                'alarm_bell'         => ['required', 'string', 'max:50'],
                'battery_backup'     => ['required', 'string', 'max:50'],
                'uji_fungsi'         => ['required', 'string', 'max:50'],
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
            // DB columns for fire alarm kartu
            $dbColumns = ['panel_kontrol', 'detector', 'manual_call_point', 'alarm_bell', 'battery_backup', 'uji_fungsi'];

            // Mapping by KEY (if template has keys set)
            $keyMapping = [
                'panel_kontrol' => 'panel_kontrol',
                'panel' => 'panel_kontrol',
                'panel_alarm' => 'panel_kontrol',
                'detector' => 'detector',
                'manual_call_point' => 'manual_call_point',
                'alarm_bell' => 'alarm_bell',
                'bell' => 'alarm_bell',
                'battery_backup' => 'battery_backup',
                'uji_fungsi' => 'uji_fungsi',
                'kondisi_fisik' => 'uji_fungsi',
                'kabel' => 'battery_backup',
            ];

            // Mapping by LABEL (case-insensitive, partial match)
            $labelMapping = [
                'panel' => 'panel_kontrol',
                'detector' => 'detector',
                'bell' => 'alarm_bell',
                'sirine' => 'alarm_bell',
                'manual call' => 'manual_call_point',
                'kabel' => 'battery_backup',
                'instalasi' => 'battery_backup',
                'kondisi fisik' => 'uji_fungsi',
                'uji fungsi' => 'uji_fungsi',
                'battery' => 'battery_backup',
            ];
            
            // Initialize all required fields with default value
            foreach ($dbColumns as $field) {
                if (!isset($data[$field])) {
                    $data[$field] = '-';
                }
            }
            
            foreach ($template->inspection_fields as $index => $field) {
                $fieldName = 'inspection_' . $index;
                if (isset($data[$fieldName])) {
                    $fieldKey = $field['key'] ?? null;
                    $fieldLabel = strtolower($field['label'] ?? '');
                    $dbColumn = null;

                    // Try mapping by key first
                    if ($fieldKey && isset($keyMapping[$fieldKey])) {
                        $dbColumn = $keyMapping[$fieldKey];
                    }

                    // Fallback: map by label using partial match
                    if (!$dbColumn) {
                        foreach ($labelMapping as $needle => $col) {
                            if (str_contains($fieldLabel, $needle)) {
                                $dbColumn = $col;
                                break;
                            }
                        }
                    }

                    // Last resort: map by position (index -> dbColumn)
                    if (!$dbColumn && isset($dbColumns[$index])) {
                        $dbColumn = $dbColumns[$index];
                    }

                    if ($dbColumn) {
                        $data[$dbColumn] = $data[$fieldName];
                    }
                    unset($data[$fieldName]);
                }
            }
        }

        // Tambahkan user_id
        $data['user_id'] = auth()->id();

        $latestKartu = KartuFireAlarm::where('fire_alarm_id', $data['fire_alarm_id'])
            ->orderBy('revisi', 'desc')
            ->first();

        $data['revisi'] = $this->computeNextRevisi($latestKartu);
        
        // Log final data before insert
        \Log::info('Final data before insert', ['data' => $data]);
        
        // Simpan kartu inspeksi Fire Alarm
        KartuFireAlarm::create($data);
        
        return redirect()
            ->route('fire-alarm.index')
            ->with('success', 'Kartu Kendali Fire Alarm berhasil disimpan dan menunggu approval');
    }
}






