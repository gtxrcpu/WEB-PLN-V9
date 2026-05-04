<?php
require __DIR__.'/vendor/autoload.php';
\ = require_once __DIR__.'/bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();

\ = \App\Models\KartuApar::whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNull('leader_rejected_at')
            ->count();
echo 'Admin Approval logic pending APAR: ' . \ . " n;
