<?php
//ini_set('display_errors', 'On'); // сообщения с ошибками будут показываться
//error_reporting(E_ALL); // E_ALL - отображаем ВСЕ ошибки
require_once 'lib.php';

$contextName = 'IFRAME';
require_once 'user-context-loader.inc.php';

$app = AppInstance::loadApp($accountId);

$isSettingsRequired = $app->status != AppInstance::ACTIVATED;

require 'iframe.html';