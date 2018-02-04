<?php
use unreal4u\TelegramAPI\Abstracts\TelegramMethods;
use unreal4u\TelegramAPI\Abstracts\TelegramTypes;

class mainBot{
  protected $logger; // LoggerInterface
  protected $httpClient; // Client
  protected $token = BOT_TOKEN; // string
  protected $userID = 0; // int
  protected $chatID = 0; // int

  /* Action to perform at /start
  /* @return SendMessage
  */
  protected function start(): SendMessage
  {
    $this->logger->debug('[CMD Inside start]');
    $this->response->text = "Bienvenido al Bot administrador de Cuentos x Telefono 2018, pon /help para ver una lista de los comandos disponibles";

    return $this->response;
  }
}
