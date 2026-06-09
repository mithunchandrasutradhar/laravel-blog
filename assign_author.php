<?php
require '/var/www/vendor/autoload.php';
$app = require '/var/www/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$u = App\Models\User::where('email', 'author@myblog.com')->first();
if (!$u) { die("User not found\n"); }
$u->syncRoles(['author']);
echo "Roles: " . $u->getRoleNames()->implode(',') . "\n";
