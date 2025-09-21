<?php
chdir('/var/www/html');
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ftp = config('filesystems.disks.ftp');
if (!$ftp) { echo "no-ftp-config\n"; exit(1); }
$host = $ftp['host'] ?? '';
$user = $ftp['username'] ?? '';
$pass = $ftp['password'] ?? '';
$port = $ftp['port'] ?? 21;

echo "FTP config from Laravel (masked):\n";
echo "host: " . ($host?:'<empty>') . "\n";
echo "username: " . ($user?:'<empty>') . "\n";
echo "password: " . ($pass?'<set>':'<empty>') . "\n";

$c = @ftp_connect($host, (int)$port, 10);
if (!$c) { echo "connect-failed\n"; exit(2); }
if (!@ftp_login($c, $user, $pass)) { echo "login-failed\n"; ftp_close($c); exit(3); }

echo "login-ok\n";
ftp_close($c);
