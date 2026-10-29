<?php

declare(strict_types=1);

require_once __DIR__ . '/../../htdocs/app/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\AppException;
use App\Core\Env;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

Autoloader::register();
Autoloader::addNamespace('App', dirname(__DIR__, 2) . '/htdocs/app');

// Configure Response for testing so it doesn't exit
Response::$exitOnSend = false;

$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $testName): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "[PASS] {$testName}\n";
    } else {
        $failed++;
        echo "[FAIL] {$testName}\n";
    }
}

echo "=== Running BE-001 Core Tests ===\n\n";

// 1. Autoloader & AppException Test
$exception = new AppException('TEST_ERROR', 'Pesan error uji', 400, ['field1' => 'tidak valid']);
assertCondition($exception->getErrorCode() === 'TEST_ERROR', 'AppException errorCode matches');
assertCondition($exception->getHttpStatus() === 400, 'AppException httpStatus matches');
assertCondition($exception->getFields() === ['field1' => 'tidak valid'], 'AppException fields matches');

// 2. Env Test
$testEnvFile = __DIR__ . '/.env.test';
file_put_contents($testEnvFile, "
# Test comment
APP_ENV=testing
APP_DEBUG=true
PORT=8000
SECRET=\"quoted_value\"
INLINE_COMMENT=hello # this is a comment
NULL_VAL=null
FALSE_VAL=false
");
Env::load($testEnvFile);
unlink($testEnvFile);

assertCondition(Env::get('APP_ENV') === 'testing', 'Env::get loads string value');
assertCondition(Env::get('APP_DEBUG') === true, 'Env::get casts boolean true');
assertCondition(Env::get('PORT') === '8000', 'Env::get loads number as string/value');
assertCondition(Env::get('SECRET') === 'quoted_value', 'Env::get strips quotes');
assertCondition(Env::get('INLINE_COMMENT') === 'hello', 'Env::get strips inline comments');
assertCondition(Env::get('NULL_VAL') === null, 'Env::get casts null');
assertCondition(Env::get('FALSE_VAL') === false, 'Env::get casts boolean false');
assertCondition(Env::get('NON_EXISTENT', 'default_val') === 'default_val', 'Env::get fallback default');

// Reload actual config/.env for subsequent database and integration tests
$actualEnvPath = dirname(__DIR__, 2) . '/htdocs/config/.env';
if (file_exists($actualEnvPath)) {
    Env::load($actualEnvPath);
}

// 3. Response Envelope Test
ob_start();
Response::ok(['id' => 1, 'name' => 'Produk A'], ['page' => 1, 'total' => 10]);
$outputOk = ob_get_clean();
$jsonOk = json_decode($outputOk, true);
assertCondition(
    isset($jsonOk['success']) && $jsonOk['success'] === true &&
    isset($jsonOk['data']['id']) && $jsonOk['data']['id'] === 1 &&
    isset($jsonOk['meta']['page']) && $jsonOk['meta']['page'] === 1,
    'Response::ok produces valid API Contract envelope with meta'
);

ob_start();
Response::error('VALIDATION_ERROR', 'Periksa kembali isian Anda.', 422, ['email' => 'Format salah']);
$outputErr = ob_get_clean();
$jsonErr = json_decode($outputErr, true);
assertCondition(
    isset($jsonErr['success']) && $jsonErr['success'] === false &&
    $jsonErr['error']['code'] === 'VALIDATION_ERROR' &&
    $jsonErr['error']['fields']['email'] === 'Format salah',
    'Response::error produces valid 422 envelope with fields'
);

// 4. Router & Middleware Test
$router = new Router();
$middlewareExecuted = false;

$testMiddleware = function (Request $req, callable $next) use (&$middlewareExecuted) {
    $middlewareExecuted = true;
    $next($req);
};

$router->group('/api/v1', function (Router $r) use ($testMiddleware) {
    $r->get('/products/{id}', function (Request $req) {
        Response::ok([
            'product_id' => (int) $req->getRouteParam('id'),
        ]);
    }, [$testMiddleware]);

    $r->post('/cart/items', function (Request $req) {
        Response::created([
            'added' => true,
        ]);
    });
});

// Test GET /api/v1/products/42
$req1 = new Request('GET', '/api/v1/products/42');
ob_start();
$router->dispatch($req1);
$res1 = json_decode(ob_get_clean(), true);
assertCondition($middlewareExecuted === true, 'Router executes middleware pipeline');
assertCondition($res1['data']['product_id'] === 42, 'Router extracts route parameter {id}');

// Test 404
$req404 = new Request('GET', '/api/v1/unknown');
$caught404 = false;
try {
    $router->dispatch($req404);
} catch (AppException $e) {
    if ($e->getErrorCode() === 'NOT_FOUND' && $e->getHttpStatus() === 404) {
        $caught404 = true;
    }
}
assertCondition($caught404, 'Router throws 404 AppException on unknown route');

// Test 405 Method Not Allowed
$req405 = new Request('POST', '/api/v1/products/42');
$caught405 = false;
try {
    $router->dispatch($req405);
} catch (AppException $e) {
    if ($e->getErrorCode() === 'METHOD_NOT_ALLOWED' && $e->getHttpStatus() === 405) {
        $caught405 = true;
    }
}
assertCondition($caught405, 'Router throws 405 AppException on wrong method');

// 5. Logger Test
$logPath = __DIR__ . '/test.log';
Logger::setLogPath($logPath);
Logger::info('Test log entry', ['user_id' => 123]);
assertCondition(file_exists($logPath), 'Logger creates log file');
$logContent = file_get_contents($logPath);
assertCondition(str_contains($logContent, '[INFO] Test log entry {"user_id":123}'), 'Logger formats log message with context');
if (file_exists($logPath)) {
    unlink($logPath);
}

// 6. BE-002: .htaccess & Environment Scaffolding Tests
$rootHtaccess = file_get_contents(dirname(__DIR__, 2) . '/htdocs/.htaccess');
assertCondition($rootHtaccess !== false, 'htdocs/.htaccess exists and is readable');
assertCondition(str_contains($rootHtaccess, 'RewriteRule ^api/v1'), 'htdocs/.htaccess contains API front controller rewrite');
assertCondition(str_contains($rootHtaccess, 'X-Content-Type-Options "nosniff"'), 'htdocs/.htaccess sets security headers');
assertCondition(str_contains($rootHtaccess, 'RewriteRule ^(app|config|storage)/ - [F,L,NC]'), 'htdocs/.htaccess blocks internal directories');

$appHtaccess = file_get_contents(dirname(__DIR__, 2) . '/htdocs/app/.htaccess');
$configHtaccess = file_get_contents(dirname(__DIR__, 2) . '/htdocs/config/.htaccess');
$storageHtaccess = file_get_contents(dirname(__DIR__, 2) . '/htdocs/storage/.htaccess');
assertCondition(str_contains((string) $appHtaccess, 'Require all denied'), 'htdocs/app/.htaccess blocks web access');
assertCondition(str_contains((string) $configHtaccess, 'Require all denied'), 'htdocs/config/.htaccess blocks web access');
assertCondition(str_contains((string) $storageHtaccess, 'Require all denied'), 'htdocs/storage/.htaccess blocks web access');

$uploadsHtaccess = file_get_contents(dirname(__DIR__, 2) . '/htdocs/uploads/.htaccess');
assertCondition($uploadsHtaccess !== false, 'htdocs/uploads/.htaccess exists');
assertCondition(str_contains((string) $uploadsHtaccess, 'php_flag engine off'), 'htdocs/uploads/.htaccess disables PHP engine');
assertCondition(str_contains((string) $uploadsHtaccess, 'RemoveHandler'), 'htdocs/uploads/.htaccess strips script handlers');

$envExample = file_get_contents(dirname(__DIR__, 2) . '/htdocs/config/.env.example');
assertCondition(str_contains((string) $envExample, 'DB_NAME='), 'config/.env.example contains DB_NAME');
assertCondition(str_contains((string) $envExample, 'PUSHER_KEY='), 'config/.env.example contains PUSHER_KEY');
assertCondition(str_contains((string) $envExample, 'SESSION_NAME='), 'config/.env.example contains SESSION_NAME');

// 7. BE-003: Session, CSRF, and Auth/Config Endpoints
use App\Core\Session;
use App\Core\Csrf;
use App\Middleware\CsrfMiddleware;
use App\Controllers\ConfigController;
use App\Controllers\AuthController;

// Session test
Session::start();
Session::set('test_key', 'test_value');
assertCondition(Session::get('test_key') === 'test_value', 'Session stores and retrieves data');
Session::remove('test_key');
assertCondition(Session::get('test_key') === null, 'Session removes key');

// CSRF test
$csrfToken = Csrf::getToken();
assertCondition(!empty($csrfToken) && strlen($csrfToken) === 64, 'Csrf::getToken produces 64-char hex token');
assertCondition(Csrf::validate($csrfToken) === true, 'Csrf::validate accepts valid token');
assertCondition(Csrf::validate('invalid_token') === false, 'Csrf::validate rejects invalid token');
assertCondition(Csrf::validate('') === false, 'Csrf::validate rejects empty token');

// CsrfMiddleware test
$csrfMiddleware = new CsrfMiddleware();
$middlewareCalled = false;
$nextClosure = function ($req) use (&$middlewareCalled) {
    $middlewareCalled = true;
};

// GET request should pass without CSRF token
$getReq = new Request('GET', '/api/v1/auth/csrf');
$csrfMiddleware->handle($getReq, $nextClosure);
assertCondition($middlewareCalled === true, 'CsrfMiddleware allows GET request without CSRF token');

// POST request without token should throw 403
$middlewareCalled = false;
$postReqNoCsrf = new Request('POST', '/api/v1/cart/items');
$csrfBlocked = false;
try {
    $csrfMiddleware->handle($postReqNoCsrf, $nextClosure);
} catch (AppException $e) {
    if ($e->getErrorCode() === 'CSRF_INVALID' && $e->getHttpStatus() === 403) {
        $csrfBlocked = true;
    }
}
assertCondition($csrfBlocked === true, 'CsrfMiddleware rejects POST without CSRF token with 403 CSRF_INVALID');

// POST request with valid token should pass
$middlewareCalled = false;
$postReqValidCsrf = new Request('POST', '/api/v1/cart/items', null, null, ['x-csrf-token' => $csrfToken]);
$csrfMiddleware->handle($postReqValidCsrf, $nextClosure);
assertCondition($middlewareCalled === true, 'CsrfMiddleware allows POST with valid X-CSRF-Token');

// API Dispatch Test: GET /api/v1/config/public
$router = new Router();
require dirname(__DIR__, 2) . '/htdocs/app/routes.php';

$reqConfig = new Request('GET', '/api/v1/config/public');
ob_start();
$router->dispatch($reqConfig);
$resConfig = json_decode(ob_get_clean(), true);
assertCondition(
    isset($resConfig['success']) && $resConfig['success'] === true &&
    isset($resConfig['data']['site_name']) &&
    isset($resConfig['data']['service_fee_percent']) &&
    isset($resConfig['data']['categories']) && is_array($resConfig['data']['categories']),
    'GET /api/v1/config/public returns valid config according to contract'
);

// API Dispatch Test: GET /api/v1/auth/csrf
$reqCsrf = new Request('GET', '/api/v1/auth/csrf');
ob_start();
$router->dispatch($reqCsrf);
$resCsrf = json_decode(ob_get_clean(), true);
assertCondition(
    isset($resCsrf['success']) && $resCsrf['success'] === true &&
    !empty($resCsrf['data']['csrf_token']),
    'GET /api/v1/auth/csrf returns csrf_token according to contract'
);

// API Dispatch Test: GET /api/v1/auth/me (as guest)
Session::remove('user_id');
$reqMeGuest = new Request('GET', '/api/v1/auth/me');
ob_start();
$router->dispatch($reqMeGuest);
$resMeGuest = json_decode(ob_get_clean(), true);
assertCondition(
    isset($resMeGuest['success']) && $resMeGuest['success'] === true &&
    $resMeGuest['data']['user'] === null &&
    !empty($resMeGuest['data']['csrf_token']) &&
    $resMeGuest['data']['unread_notifications'] === 0 &&
    $resMeGuest['data']['unread_messages'] === 0,
    'GET /api/v1/auth/me returns guest payload with csrf_token and 0 counters'
);

// API Dispatch Test: GET /api/v1/auth/me (authenticated as admin id=1)
Session::set('user_id', 1);
$reqMeAuth = new Request('GET', '/api/v1/auth/me');
ob_start();
$router->dispatch($reqMeAuth);
$resMeAuth = json_decode(ob_get_clean(), true);
assertCondition(
    isset($resMeAuth['success']) && $resMeAuth['success'] === true &&
    isset($resMeAuth['data']['user']['id']) && $resMeAuth['data']['user']['id'] === 1 &&
    $resMeAuth['data']['user']['role'] === 'ADMIN' &&
    array_key_exists('store_status', $resMeAuth['data']['user']),
    'GET /api/v1/auth/me returns authenticated User object matching contract'
);
Session::remove('user_id');

echo "\nSummary: {$passed} passed, {$failed} failed.\n";

if ($failed > 0) {
    exit(1);
}
