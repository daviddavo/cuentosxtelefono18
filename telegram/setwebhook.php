<?php
require __DIR__.'/00.common.php';

use \React\EventLoop\Factory;
use \unreal4u\TelegramAPI\HttpClientRequestHandler;
use \unreal4u\TelegramAPI\TgLog;
use \unreal4u\TelegramAPI\Telegram\Methods\SetWebhook;
use unreal4u\TelegramAPI\Telegram\Types\Custom\InputFile;
use \unreal4u\TelegramAPI\Telegram\Methods\GetWebhookInfo;
use unreal4u\TelegramAPI\Telegram\Types\WebhookInfo;
use \unreal4u\TelegramAPI\Telegram\Types\Update;

$loop = Factory::create();

$setWebhook = new SetWebhook();
$setWebhook->url = WEBHOOK_URL;
// $setWebhook->certificate = new InputFile(CUSTOM_CERTIFICATE);

$tgLog = new TgLog(BOT_TOKEN, new HttpClientRequestHandler($loop));
$tgLog->performApiRequest($setWebhook);

// Getting POST request body and decoding it from JSON to associative array
$updateData = json_decode(file_get_contents('php://input'), true);

$webHookInfo = new GetWebhookInfo();
$promise = $tgLog->performApiRequest($webHookInfo);

$promise->then(
  function (WebhookInfo $info) {
    global $log;
    $log->info(var_export($info,true));
  },
  function (\Exception $e){
    global $log;
    $log->error($e);
  }
);

$loop->run();
