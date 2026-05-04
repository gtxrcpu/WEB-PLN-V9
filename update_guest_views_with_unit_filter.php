<?php
/**
 * Script to update all guest views with unit filter component
 */

echo "=== UPDATING GUEST VIEWS WITH UNIT FILTER ===\n\n";

$modules = [
    'apat' => 'APAT',
    'p3k' => 'P3K',
    'apab' => 'APAB',
    'fire-alarm' => 'Fire Alarm',
    'box-hydrant' => 'Box Hydrant',
    'rumah-pompa' => 'Rumah Pompa',
];

$searchBoxPattern = '/{{-- Search Box --}}.*?<\/div>/s';
$unitFilterComponent = '{{-- Unit Filter & Search Box --}}
        <x-guest.unit-filter :units="$units" :selectedUnit="$selectedUnit" module="%s" />';

$scriptPattern = '/<script>.*?function filterItems\(\).*?<\/script>/s';

foreach ($modules as $module => $label) {
    $viewPath = "resources/views/guest/{$module}/index.blade.php";
    
    if (!file_exists($viewPath)) {
        echo "⚠ Skipping {$label}: File not found at {$viewPath}\n";
        continue;
    }
    
    echo "Processing {$label}...\n";
    
    $content = file_get_contents($viewPath);
    
    // Replace search box with unit filter component
    $componentCode = sprintf($unitFilterComponent, $module);
    $content = preg_replace($searchBoxPattern, $componentCode, $content, 1, $count);
    
    if ($count > 0) {
        echo "  ✓ Replaced search box with unit filter component\n";
    } else {
        echo "  ⚠ Could not find search box pattern\n";
    }
    
    // Remove old script if exists
    $content = preg_replace($scriptPattern, '', $content, 1, $scriptCount);
    
    if ($scriptCount > 0) {
        echo "  ✓ Removed old filter script (now in component)\n";
    }
    
    // Save updated content
    file_put_contents($viewPath, $content);
    echo "  ✓ Saved {$viewPath}\n\n";
}

echo "=== UPDATE COMPLETE ===\n";
echo "All guest views have been updated with unit filter functionality.\n";
echo "\nNext steps:\n";
echo "1. Test each module at /guest/apar, /guest/apat, etc.\n";
echo "2. Verify unit dropdown works correctly\n";
echo "3. Check real-time filtering updates stats\n";
echo "4. Ensure search functionality still works\n";