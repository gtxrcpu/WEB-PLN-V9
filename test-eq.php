<?php
$fp = \App\Models\FloorPlan::find(2);
$ctrl = new \App\Http\Controllers\FloorPlanController();
$res = $ctrl->getEquipmentData($fp);
echo $res->getContent();
