#!/bin/bash

echo "======================================"
echo "  DIAGNOSTIC: Unit & APAR Data Check  "
echo "======================================"
echo ""

echo "1. Checking Units..."
php artisan tinker --execute="
\$units = DB::table('units')->select('id', 'name', 'code')->get();
foreach (\$units as \$unit) {
    echo \"Unit ID: {\$unit->id}, Name: {\$unit->name}, Code: {\$unit->code}\n\";
}
"

echo ""
echo "2. Checking APAR data (first 10)..."
php artisan tinker --execute="
\$apars = DB::table('apars')->select('id', 'serial_no', 'barcode', 'unit_id')->limit(10)->get();
foreach (\$apars as \$apar) {
    \$unitName = \$apar->unit_id ? DB::table('units')->where('id', \$apar->unit_id)->value('name') : 'Induk';
    echo \"APAR: {\$apar->serial_no}, Unit ID: \" . (\$apar->unit_id ?? 'NULL') . \" ({\$unitName})\n\";
}
"

echo ""
echo "3. Checking Users (Induk vs Units)..."
php artisan tinker --execute="
\$users = DB::table('users')->select('id', 'name', 'email', 'unit_id', 'role')->get();
foreach (\$users as \$user) {
    \$unitName = \$user->unit_id ? DB::table('units')->where('id', \$user->unit_id)->value('name') : 'Induk';
    echo \"User: {\$user->name}, Email: {\$user->email}, Role: {\$user->role}, Unit ID: \" . (\$user->unit_id ?? 'NULL') . \" ({\$unitName})\n\";
}
"

echo ""
echo "4. APAR Count per Unit..."
php artisan tinker --execute="
\$counts = DB::table('apars')
    ->select('unit_id', DB::raw('COUNT(*) as total'))
    ->groupBy('unit_id')
    ->get();
foreach (\$counts as \$count) {
    \$unitName = \$count->unit_id ? DB::table('units')->where('id', \$count->unit_id)->value('name') : 'Induk';
    echo \"Unit ID: \" . (\$count->unit_id ?? 'NULL') . \" ({\$unitName}): {\$count->total} APAR\n\";
}
"

echo ""
echo "======================================"
echo "  Diagnostic Complete!  "
echo "======================================"
