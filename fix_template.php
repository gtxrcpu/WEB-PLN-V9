<?php
require 'vendor/autoload.php';
\ = require_once 'bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();

\ = App\Models\KartuTemplate::where('module', 'apar')->first();
if (\) {
    \ = \->inspection_fields;
    \ = [
        'Kondisi Tabung' => 'kondisi_tabung',
        'Kondisi Selang' => 'kondisi_selang',
        'Kondisi Pin Pengaman' => 'kondisi_pin',
        'Tekanan' => 'tekanan',
        'Berat' => 'berat',
        'Catatan' => 'catatan',
    ];
    \ = false;
    foreach (\ as &\) {
        if (!isset(\['key']) && isset(\[\['label']])) {
            \['key'] = \[\['label']];
            \ = true;
        }
    }
    if (\) {
        \->inspection_fields = \;
        \->save();
        echo " Fixed APAR template.\n\;
