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
    $update = new Update($updateData);
    $this->logger->info("webHook activado: " . json_encode($update, true));
    $this->logger->info(json_encode($update, true));

    $args = [$this->tgLog, $this->logger, $this->db];
    if(isset($update->message)){
      $rango = 0;
      if(strpos($update->message->text, "start") === false){
        $rango = $this->db->getUser($update->message->from->id)["rango"];
      }
      $user = createFromRango($rango, $update->message->from, ...$args);
      $user->exec($update->message);
    } else if(isset($update->callback_query)) {
      // Asumimos que el usuario existe
      $rango = $this->db->getUser($update->callback_query->from->id)["rango"];
      $user = createFromRango($rango, $update->callback_query->from, ...$args);
      $user->callback_query($update->callback_query);
    } else {
      $this->logger->error("That update type is not implemented");
    }

  }

  final private function getMe(): User
  {
    $this->logger->info('Requesting a getMe');
    $tgLog = new TgLog($this->token, $this->logger, $this->httpClient);
    return $tgLog->performApiRequest(new GetMe());
  }
}
