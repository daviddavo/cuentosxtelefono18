<?php
use \unreal4u\TelegramAPI\HttpClientRequestHandler;
use \Monolog\Logger;
use \unreal4u\TelegramAPI\Abstracts\TelegramMethods;
use \unreal4u\TelegramAPI\Abstracts\TelegramTypes;
use \unreal4u\TelegramAPI\Telegram\Types\Update;
use \unreal4u\TelegramAPI\TgLog;

include "users.php";

class mainBot{
  protected $logger; // LoggerInterface
  protected $token; // string
  protected $httpClient; // loop
  protected $userID = 0; // int
  protected $chatID = 0; // int
  protected $tgLog; // tgLog

  final public function __construct(
    string $token,
    Logger $logger,
    HttpClientRequestHandler $httpClient,
    database $db
  ){
    $this->token = $token;
    $this->logger = $logger;
    $this->httpClient = $httpClient;

    $this->db = $db;
    $this->tgLog = new TgLog($token, $httpClient, $logger);

    $this->logger->debug('Finished constructing bot');
  }

  public function webHookHandler(array $updateData){
    $this->logger->info("webHook activado: " . json_encode($updateData, true));
    $update = new Update($updateData);
    $this->logger->info(var_export($update, true));

    // TODO: Switch con cada tipo de usuario
    $user = new BaseUser($update->message->from, $this->tgLog, $this->logger, $this->db);
    $user->exec($update);
  }

  final private function getMe(): User
  {
    $this->logger->info('Requesting a getMe');
    $tgLog = new TgLog($this->token, $this->logger, $this->httpClient);
    return $tgLog->performApiRequest(new GetMe());
  }
}
