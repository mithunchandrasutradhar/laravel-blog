<?php
require '/var/www/vendor/autoload.php';
$app = require '/var/www/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$disk = config('filesystems.default');
echo "Default disk: $disk\n";
\Illuminate\Support\Facades\Storage::disk('public')->put('test-upload.txt', 'ok');
echo "File exists: " . (\Illuminate\Support\Facades\Storage::disk('public')->exists('test-upload.txt') ? 'yes' : 'no') . "\n";
echo "URL: " . \Illuminate\Support\Facades\Storage::disk('public')->url('test-upload.txt') . "\n";
\Illuminate\Support\Facades\Storage::disk('public')->delete('test-upload.txt');
echo "Storage OK\n";
