<?php
  require_once __DIR__.'/00.common.php';

  use \unreal4u\TelegramAPI\Telegram\Types\Update;
  use \unreal4u\TelegramAPI\Telegram\Methods\SendMessage;

  class BaseUser {
    public $id = 0;
    static public $rango; /* Rango de la clase */
    // Comandos disponibles del usuario. Usado también en testing

    public function __construct($user, $tgLog, $logger, $db){
      $this->id = $user->id;
      $this->username = $user->username;
      $this->tgLog = $tgLog;
      $this->logger = $logger;
      $this->db = $db;
      $this->rango = 0;
      $this->available_commands = [
        "start" => "Inicia el bot",
        "help" => "Muestra esta ayuda"
      ];

      $this->logger->debug("Finished building user");
    }

    final public function ranStr(int $rango){
      switch($rango):
        case 100:
          return "admin";
        default:
          return "usuario";
      endswitch;
    }

    final private function sendSimpleMsg(string $text){
      $response = new SendMessage();
      $response->text = $text;
      $response->chat_id = $this->id;
      $this->logger->info("Sending message: ".json_encode($response));
      return $this->tgLog->performApiRequest($response);
    }

    public function exec(Update $update){
      // Update las connection
      // TODO: Test which error returns
      $this->db->update_connection($this->id);

      $command = trim($update->message->text, " \t\n\r\0\x0B/");
      $this->logger->info("Executing command $command");
      switch($command):
        case "start":
          return $this->start($update);
        case "help":
          return $this->help($update);
        default:
          $this->logger->info("Command {$command} not implemented, available commands: ".json_encode($this->available_commands));
          return false;
      endswitch;
    }

    final private function start(Update $update){
      $startText = <<<EOS
Bienvenido al Bot administrador de Cuentos x Telefono 2018, pon /help para ver una lista de los comandos disponibles
Este servidor usa 'rangos', por lo que tendrás que hablar con @DSinapellido para que te ponga el rango que quieres y poder empezar a administrar
EOS;
      if(!$this->db->userExists($this->id)){
        $this->logger->info("User {$this->id} (@{$this->username}) doesn't exist, creating");
        $this->db->insertUser($this->id, $this->username);
      }

      return $this->sendSimpleMsg($startText);
    }

    final private function help(Update $update){
      $ran = $this->ranStr($this->rango);
      $helpText = "Eres {$ran} y tus comandos disponibles son:\n";
      foreach($this->available_commands as $command => $text){
        $helpText .= "/{$command}: {$text}\n";
      }
      return $this->sendSimpleMsg($helpText);
    }
  }

  class Admin extends BaseUser{
    
  }
