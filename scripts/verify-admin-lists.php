<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::where('username', 'superadmin')->first();

if (! $user) {
    echo "superadmin user not found\n";
    exit(1);
}

Auth::login($user);

$routes = collect(Illuminate\Support\Facades\Route::getRoutes())
    ->filter(fn ($route) => str_starts_with($route->uri(), 'admin/')
        && ! str_contains($route->uri(), '{')
        && ! str_contains($route->uri(), 'login')
        && in_array('GET', $route->methods(), true))
    ->map(fn ($route) => '/'.$route->uri())
    ->unique()
    ->sort()
    ->values()
    ->all();

$failed = 0;

foreach ($routes as $route) {
    $request = Illuminate\Http\Request::create($route, 'GET');
    $request->setLaravelSession(app('session.store'));
    app('session.store')->start();
    Auth::login($user);

    $response = $app->handle($request);
    $status = $response->getStatusCode();
    $ok = $status >= 200 && $status < 400;

    echo ($ok ? 'OK' : 'FAIL')." [$status] $route\n";

    if (! $ok) {
        $failed++;
        $content = $response->getContent();
        if (preg_match('/Class \\"([^\\"]+)\\" not found/', $content, $matches)) {
            echo '  -> Class not found: '.$matches[1]."\n";
        } elseif (preg_match('/intl.*?format/s', $content)) {
            echo "  -> intl error still present\n";
        } elseif (preg_match('/exception-message[^>]*>(.*?)<\/div>/s', $content, $matches)) {
            $message = trim(strip_tags($matches[1]));
            if ($message !== '') {
                echo '  -> '.$message."\n";
            }
        }
    }
}

echo PHP_EOL.'Total: '.count($routes).', Failed: '.$failed.PHP_EOL;

exit($failed > 0 ? 1 : 0);
