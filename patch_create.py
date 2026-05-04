import re

ctrl = '/home/ramadhan/plnweb/app/Http/Controllers/P3kController.php'
c = open(ctrl).read()

new_create = '''    public function create(Request )
    {
         = ->query('jenis', 'pemeriksaan');
         = \App\Models\Unit::orderBy('name')->get();
        
        // Calculate next number for each unit for P3K-PKS format
         = [];
        foreach ( as ) {
             = strtoupper(substr(->code ?? ->name, 0, 3));
             = 'P3K-PKS-' .  . '-';
             = \App\Models\P3k::where('serial_no', 'like',  . '%')
                ->orderByRaw('CAST(SUBSTRING_INDEX(serial_no, " -\,
