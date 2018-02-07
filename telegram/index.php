<?php
/* This file (index) handles the requests */
require_once __DIR__.'/00.common.php';

// use GuzzleHttp\Client;
use React\EventLoop\Factory;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use \unreal4u\TelegramAPI\HttpClientRequestHandler;

include "bot.php";
include "db.php";
// use \unreal4u\TelegramAPI\Telegram\Types\Update;

$logger = new Logger('cxtbot');
$logger->pushHandler(new RotatingFileHandler(__DIR__ . "/../logs/phplog.log", 30, Logger::DEBUG));

$loop = Factory::create();
$httpClient = new HttpClientRequestHandler($loop);
$db = new database(DB_SERVER, DB_USER, DB_PASSWORD, $logger);

$updateData = json_decode(file_get_contents('php://input'), true);

// Ahora a llamar al bot para que procese $updateData
try {
  $bot = new mainBot(BOT_TOKEN, $logger, $httpClient, $db);
  $bot->webHookHandler($updateData);
} catch (Exception $e) {
  $logger->error("EXCEPTION: " . $e->getMessage());
}

$db->close();
echo "Esto funciona, no?";
$loop->run();
