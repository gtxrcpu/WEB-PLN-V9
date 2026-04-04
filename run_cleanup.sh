#!/bin/bash
cd /home/ramadhan/plnweb
./vendor/bin/sail php fix_units.php
./vendor/bin/sail artisan db:seed --class=AdminSeeder --force
echo "Done cleaning units and seeding."
