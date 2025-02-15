<?php
/**
 * User: Wajdi Jurry
 * Date: 18/08/18
 * Time: 05:27 م
 */

use Phalcon\Di;
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;

ini_set("display_errors", 1);
error_reporting(E_ALL);

define("ROOT_PATH", __DIR__);
define("APP_PATH", ROOT_PATH . '/../app');

set_include_path(
    ROOT_PATH . PATH_SEPARATOR . get_include_path()
);

// Required for phalcon/incubator
include APP_PATH . "/vendor/autoload.php";

// Use the application autoloader to autoload the classes
// Autoload the dependencies found in composer
$loader = new Loader();
$loader->registerDirs(
    [
        APP_PATH,
        ROOT_PATH
    ]
);

$loader->registerNamespaces([
    'app\common\models' => APP_PATH . '/common/models',
    'app\common\repositories' => APP_PATH . '/common/repositories',
    'app\common\interfaces' => APP_PATH . '/common/interfaces/',
    'app\common\traits' => APP_PATH . '/common/traits',
    'app\common\services' => APP_PATH . '/common/services',
    'app\common\services\cache' => APP_PATH . '/common/services/cache',
    'app\common\requestHandler' => APP_PATH . '/common/requestHandler',
    'app\common\requestHandler\product' => APP_PATH . '/common/requestHandler/product',
    'app\modules\api\controllers' => APP_PATH . '/modules/api/controllers',
    'app\common\utils' => APP_PATH . '/common/utils',
    'app\common\logger' => APP_PATH . '/common/logger/',
    'app\common\enums' => APP_PATH . '/common/enums',
    'tests' => ROOT_PATH . '/'
]);

$loader->registerClasses([
    'app\common\exceptions\OperationFailed' => APP_PATH . '/common/exceptions/OperationFailed.php',
    'app\common\exceptions\NotFound' => APP_PATH . '/common/exceptions/NotFound.php'
]);

$loader->register();

$di = new FactoryDefault();

Di::reset();
Di::setDefault($di);
