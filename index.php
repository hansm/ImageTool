<?php
/**
 * Image Tool
 *
 * @author Hans Mäesalu (hansmaesalu@gmail.com)
 * @license BSD Simplified https://opensource.org/licenses/BSD-3-Clause
 * @version 1
 */

use ImageTool\Log;
use ImageTool\ViewManager;

define('U_TIME', time());
define('ROOT', __DIR__ . DIRECTORY_SEPARATOR);

spl_autoload_register(function($className) {
	try {
		include ROOT . str_replace('\\', DIRECTORY_SEPARATOR, $className) .'.php';
	} catch (ErrorException $e) {
		Log::get()->warn('Could not load class '. $className, $e);
	}
});

error_reporting(E_ALL ^ E_NOTICE);

set_error_handler(function($errNo, $errStr, $errFile, $errLine) {
	throw new ErrorException($errStr, 0, $errNo, $errFile, $errLine);
}, E_ALL ^ E_NOTICE);

require ROOT . 'Settings.php';

$controller = 'Main';
$action = 'Main';

if (!empty($_GET['page'])) {
	$controller = trim($_GET['page']);
}
if (!empty($_GET['action'])) {
	$action = trim($_GET['action']);
}

$controllerClass = '\\ImageTool\\Controller\\'. $controller .'Controller';
$actionMethod = 'action'. $action;

if (!preg_match('#[a-zA-Z]{1,20}#', $controller)
		|| !class_exists($controllerClass)
		|| !preg_match('#[a-zA-Z]{1,20}#', $action)) {
	Log::get()->debug('Controller does not exist: '. $controllerClass .'->'. $actionMethod);
	ViewManager::errorPage('Page not found.', ViewManager::CODE_NOT_FOUND);
	exit();
}

$controllerObject = new $controllerClass($settings);

if (!method_exists($controllerObject, $actionMethod)) {
	Log::get()->debug('Action does not exist: '. $controllerClass .'->'. $actionMethod);
	ViewManager::errorPage('Page not found.', ViewManager::CODE_NOT_FOUND);
	exit();
}

$controllerObject->{$actionMethod}();

try {
	$controllerObject->output();
} catch (\ImageTool\ViewException $e) {
	Log::get()->error('Failed to create view.', $e);
	ViewManager::errorPage('Page not found.', ViewManager::CODE_NOT_FOUND);
	exit();
}
