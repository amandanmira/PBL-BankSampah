<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::where('role', 'manager')->first();
if ($user) {
    echo $user->createToken('test')->plainTextToken;
} else {
    echo "NO USER";
}
