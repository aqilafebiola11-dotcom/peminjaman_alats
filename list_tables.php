<?php
require 'vendor/autoload.php';
$app = require_once('bootstrap/app.php');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = DB::select('SHOW TABLES');
echo "Tables in the database:\n";
foreach($tables as $t) {
    $table = (array)$t;
    $tableName = array_values($table)[0];
    echo "  - $tableName\n";
}
