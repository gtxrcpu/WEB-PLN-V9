<?php
$fp = \App\Models\FloorPlan::find(2);
if ($fp) {
    echo json_encode($fp->getAllEquipment()['cctv']->toArray(), JSON_PRETTY_PRINT);
} else {
    echo "FloorPlan not found";
}
