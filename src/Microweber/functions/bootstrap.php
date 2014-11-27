<?php

if (version_compare(phpversion(), "5.3.0", "<=")) {
    exit("Error: You must have PHP version 5.3 or greater to run Microweber");
}


if (!defined('MW_VERSION')) {
    define('MW_VERSION', 0.96);
}
if (!defined('MW_VERSION')) {
    define('MW_VERSION', MW_VERSION);
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('MW_PATH')) {
    define('MW_PATH', realpath(__DIR__ . '/../') . DS);
}
 

if (!defined('MW_ROOTPATH')) {
    define('MW_ROOTPATH', dirname(dirname(MW_PATH)) . DS);
}

if (!defined('MW_USERFILES_FOLDER_NAME')) {
    define('MW_USERFILES_FOLDER_NAME', 'userfiles'); //relative to public dir
}
if (!defined('MW_MODULES_FOLDER_NAME')) {
    define('MW_MODULES_FOLDER_NAME', 'modules'); //relative to userfiles dir
}
if (!defined('MW_ELEMENTS_FOLDER_NAME')) {
    define('MW_ELEMENTS_FOLDER_NAME', 'elements'); //relative to userfiles dir
}
if (!defined('MW_MEDIA_FOLDER_NAME')) {
    define('MW_MEDIA_FOLDER_NAME', 'media'); //relative to userfiles dir
}


if (!defined('MW_TEMPLATES_FOLDER_NAME')) {
    define('MW_TEMPLATES_FOLDER_NAME', 'templates'); //relative to userfiles dir
}
if (!defined('MW_SYSTEM_MODULE_FOLDER')) {
    define('MW_SYSTEM_MODULE_FOLDER', 'microweber'); //relative to modules dir
}
if (!defined('MW_USER_IP')) {
    if (isset($_SERVER["REMOTE_ADDR"])) {
        define("MW_USER_IP", $_SERVER["REMOTE_ADDR"]);
    } else {
        define("MW_USER_IP", '127.0.0.1');
    }
}

include_once(__DIR__ . DS . 'paths.php');

include_once(__DIR__ . DS . 'api.php');
include_once(__DIR__ . DS . 'filesystem.php');
include_once(__DIR__ . DS . 'lang.php');
include_once(__DIR__ . DS . 'events.php');
include_once(__DIR__ . DS . 'config.php');

include_once(__DIR__ . DS . 'db.php');
include_once(__DIR__ . DS . 'user.php');
include_once(__DIR__ . DS . 'common.php');
include_once(__DIR__ . DS . 'media.php');
include_once(__DIR__ . DS . 'other.php');
include_once(__DIR__ . DS . 'content.php');
include_once(__DIR__ . DS . 'menus.php');
include_once(__DIR__ . DS . 'categories.php');
include_once(__DIR__ . DS . 'options.php');
include_once(__DIR__ . DS . 'shop.php');
include_once(__DIR__ . DS . 'modules.php');

