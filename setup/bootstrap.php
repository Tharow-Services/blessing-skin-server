<?php
/**
 * Setup Bootstraper of Blessing Skin Server
 */

// Define Base Directory
define('BASE_DIR', dirname(dirname(__FILE__)));

// Register Composer Auto Loader
require BASE_DIR.'/vendor/autoload.php';

// Load dotenv Configuration
if (file_exists(BASE_DIR."/.env")) {
    $dotenv = new \Dotenv\Dotenv(BASE_DIR);
    $dotenv->load();
} else {
    exit('错误：.env 配置文件不存在');
}

// Register Error Hanlders
$whoops = new \Whoops\Run;
$handler = new \Whoops\Handler\PrettyPageHandler;
$whoops->pushHandler($handler);
$whoops->register();

$app = new Illuminate\Foundation\Application(BASE_DIR);

Illuminate\Support\Facades\Facade::setFacadeApplication($app);

(new Illuminate\View\ViewServiceProvider($app))->register();
(new Illuminate\Foundation\Bootstrap\LoadConfiguration)->bootstrap($app);

(new Illuminate\Database\DatabaseServiceProvider($app))->register();
(new Illuminate\Filesystem\FilesystemServiceProvider($app))->register();
(new Illuminate\Foundation\Bootstrap\LoadConfiguration)->bootstrap($app);

$app->singleton('database', \App\Services\Database\Database::class);

require BASE_DIR.'/vendor/laravel/framework/src/Illuminate/Foundation/helpers.php';

// Load Aliases
$config = require BASE_DIR.'/config/app.php';

foreach ($config['aliases'] as $facade => $class) {
    class_alias($class, $facade);
}

\View::addExtension('tpl', 'blade');

// Set Default Timezone to UTC+8
date_default_timezone_set('Asia/Shanghai');



$config = require BASE_DIR.'/config/database.php';

$db_config = $config['connections']['mysql'];

// Check Database Config
@$conn = new \mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database'], $db_config['port']);

if ($conn->connect_error) {
    throw new App\Exceptions\E("无法连接至 MySQL 服务器，请检查你的配置：".$conn->connect_error, $conn->connect_errno, true);
}

// Boot Eloquent ORM
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($db_config);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Start Session
session_start();

function checkTableExist() {
    $tables = ['users', 'closets', 'players', 'textures', 'options'];

    foreach ($tables as $table_name) {
        // prefix will be added automatically
        if (!Database::hasTable($table_name)) {
            return false;
        }
    }

    return true;
}


