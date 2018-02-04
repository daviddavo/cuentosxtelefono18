<?php
use \unreal4u\TelegramAPI\HttpClientRequestHandler;
use \Monolog\Logger;
use \unreal4u\TelegramAPI\Abstracts\TelegramMethods;
use \unreal4u\TelegramAPI\Telegram\Methods\SendMessage;
use \unreal4u\TelegramAPI\Abstracts\TelegramTypes;
use \unreal4u\TelegramAPI\Telegram\Types\Update;
use \unreal4u\TelegramAPI\TgLog;

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
    $this->response = new SendMessage();

    $this->logger->debug('Finished constructing bot');
  }

  public function webHookHandler(array $updateData){
    $this->logger->info("webHook activado: " . var_export($updateData, true));
    $update = new Update($updateData);
    $this->logger->info(var_export($update, true));
    $this->userID = $update->message->from->id;

    // Update last connection
    $this->db->update_connection($this->userID);

    // TODO: Cambiar por switch
    if($update->message->text == "/start"){
      return $this->start($update);
    }
    if($update->message->text == "/help"){
      return $this->help();
    }
  }

  final private function getMe(): User
  {
    $this->logger->info('Requesting a getMe');
    $tgLog = new TgLog($this->token, $this->logger, $this->httpClient);
    return $tgLog->performApiRequest(new GetMe());
  }

  /* Action to perform at /start
  /* @return SendMessage
  */
  // TODO: Meter al usuario en la base de datos
  protected function start($update)//: SendMessage
  {
    $this->logger->debug('[CMD Inside start]');
    $this->response->text = <<<EOS
Bienvenido al Bot administrador de Cuentos x Telefono 2018, pon /help para ver una lista de los comandos disponibles
Este servidor usa 'rangos', por lo que tendrás que hablar con @DSinapellido para que te ponga el rango que quieres y poder empezar a administrar
EOS;
    $this->response->chat_id = $this->userID;

    if(!$this->db->userExists($this->userID)){
      $this->logger->info("User {$this->userID} doesn't exist, creating");
      $this->db->insertUser($this->userID, $update->message->from->username);
    }

    $this->tgLog->performApiRequest($this->response);
    return $this->response;
  }

  protected function help()
  {
    // TODO: 'Eres ... y tus comandos disponibles son:'
    $this->logger->debug('HELP shown');
    $this->response->text = "Comandos disponibles: \n\t/start Inicia el bot\n\t/help Muestra esta ayuda";
    $this->response->chat_id = $this->userID;

    $this->tgLog->performApiRequest($this->response);

    return $this->response;
  }
}
