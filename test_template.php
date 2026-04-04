<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$t = App\Models\KartuTemplate::where('module', 'apar')->first();
if ($t) {
    echo json_encode($t->inspection_fields, JSON_PRETTY_PRINT);
} else {
    echo 'No template found';
}
