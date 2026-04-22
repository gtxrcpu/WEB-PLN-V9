<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KartuTemplate;
use Illuminate\Http\Request;

class KartuTemplateController extends Controller
{
    public function index()
    {
        $templates = KartuTemplate::with('unit')->orderBy('module')->orderBy('unit_id')->get();
        $modules = KartuTemplate::getModules();
        
        return view('admin.kartu-templates.index', compact('templates', 'modules'));
    }

    public function edit($module)
    {
        $template = KartuTemplate::where('module', $module)->firstOrFail();
        $moduleName = KartuTemplate::getModules()[$module] ?? $module;
        $units = \App\Models\Unit::where('is_active', true)->orderBy('code')->get();
        
        return view('admin.kartu-templates.edit', compact('template', 'moduleName', 'units'));
    }

    public function update(Request $request, $module)
    {
        $template = KartuTemplate::where('module', $module)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
            'unit_addresses' => 'nullable|array',
            'unit_addresses.*' => 'nullable|string',
            'header_fields' => 'required|array',
            'header_fields.*.key' => 'nullable|string',
            'header_fields.*.label' => 'required|string',
            'header_fields.*.value' => 'required|string',
            'inspection_fields' => 'required|array',
            'inspection_fields.*.key' => 'nullable|string',
            'inspection_fields.*.label' => 'required|string',
            'inspection_fields.*.type' => 'required|string',
            'footer_fields' => 'required|array',
            'footer_fields.*.key' => 'nullable|string',
            'footer_fields.*.label' => 'required|string',
            'footer_fields.*.value' => 'required|string',
            'company_name' => 'nullable|string',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string',
            'company_fax' => 'nullable|string',
            'company_email' => 'nullable|email',
            'table_header' => 'nullable|string|max:255',
        ]);

        // Update alamat per unit sebagai JSON
        $unitAddressesData = [];
        if (isset($validated['unit_addresses'])) {
            foreach ($validated['unit_addresses'] as $unitId => $address) {
                if (!empty($address)) {
                    $unitAddressesData[$unitId] = $address;
                }
            }
        }

        // Update template dengan alamat per unit
        $updateData = [
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'company_name' => $validated['company_name'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'unit_address' => !empty($unitAddressesData) ? $unitAddressesData : null,
            'company_phone' => $validated['company_phone'] ?? null,
            'company_fax' => $validated['company_fax'] ?? null,
            'company_email' => $validated['company_email'] ?? null,
            'header_fields' => $validated['header_fields'],
            'inspection_fields' => $validated['inspection_fields'],
            'footer_fields' => $validated['footer_fields'],
            'table_header' => $validated['table_header'] ?? null,
        ];

        $template->update($updateData);
        
        // Clear all cache
        \Cache::flush();

        return redirect()->route('admin.kartu-templates.index')
            ->with('success', 'Template berhasil diupdate! Alamat per unit telah disimpan.');
    }

    public function create()
    {
        $modules = KartuTemplate::getModules();
        $units = \App\Models\Unit::where('is_active', true)->orderBy('code')->get();
        
        // Tidak perlu filter available modules karena sekarang bisa multiple template per module (berbeda unit)
        $availableModules = $modules;
        
        return view('admin.kartu-templates.create', compact('availableModules', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string',
            'unit_id' => 'nullable|exists:units,id',
            'unit_address' => 'nullable|string',
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
        ]);

        // Check unique combination of module + unit_id
        $exists = KartuTemplate::where('module', $validated['module'])
            ->where(function($query) use ($validated) {
                if (isset($validated['unit_id'])) {
                    $query->where('unit_id', $validated['unit_id']);
                } else {
                    $query->whereNull('unit_id');
                }
            })
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'module' => 'Template untuk module ini ' . (isset($validated['unit_id']) ? 'dan unit yang dipilih ' : '') . 'sudah ada.'
            ]);
        }

        // Simpan unit_address ke company_address juga untuk backward compatibility
        if (isset($validated['unit_address'])) {
            $validated['company_address'] = $validated['unit_address'];
        }

        // Default fields
        $validated['header_fields'] = [
            ['key' => 'no_dokumen', 'label' => 'No. Dokumen', 'value' => ''],
            ['key' => 'revisi', 'label' => 'Revisi', 'value' => ''],
            ['key' => 'tanggal', 'label' => 'Tanggal', 'value' => ''],
            ['key' => 'halaman', 'label' => 'Halaman', 'value' => ''],
        ];

        $validated['inspection_fields'] = [
            ['key' => 'kondisi', 'label' => 'Kondisi', 'type' => 'checkbox'],
            ['key' => 'catatan', 'label' => 'Catatan', 'type' => 'textarea'],
        ];

        $validated['footer_fields'] = [
            ['key' => 'lokasi', 'label' => 'Lokasi', 'value' => 'Surabaya'],
            ['key' => 'petugas_label', 'label' => 'Label Petugas', 'value' => 'Petugas Pemeriksa'],
        ];

        $validated['is_active'] = true;

        KartuTemplate::create($validated);

        return redirect()->route('admin.kartu-templates.index')
            ->with('success', 'Template berhasil dibuat!');
    }
}
