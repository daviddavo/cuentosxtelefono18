<?php
require_once __DIR__.'/00.common.php';

use \React\EventLoop\Factory;
use \unreal4u\TelegramAPI\HttpClientRequestHandler;
use \unreal4u\TelegramAPI\TgLog;
use \unreal4u\TelegramAPI\Telegram\Types\Update;

$loop = Factory::create();

$updateData = json_decode(file_get_contents('php://input'), true);
$log->info("Ha llegado un mensaje: " . var_export($updateData,true));
$update = new Update($updateData);

$loop->run();

echo "Esto funciona, no?";
