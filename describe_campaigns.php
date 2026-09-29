<?php
require __DIR__ . '/core/vendor/autoload.php';
$app = require_once __DIR__ . '/core/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cols = Illuminate\Support\Facades\DB::select("DESCRIBE campaigns");
foreach ($cols as $c) {
    echo $c->Field . " (" . $c->Type . ")\n";
}
