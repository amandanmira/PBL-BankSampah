<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$request = Illuminate\Http\Request::create('/api/cetak-laporan/pdf', 'GET');
$controller = app()->make(App\Http\Controllers\Api\Petugas\LaporanController::class);
try {
    $response = $controller->exportPdf($request);
    $content = $response->getContent();
    echo "SUCCESS, SIZE: " . strlen($content) . " bytes\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
