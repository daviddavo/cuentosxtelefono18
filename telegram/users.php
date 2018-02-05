<?php
  require_once __DIR__.'/00.common.php';

  use \unreal4u\TelegramAPI\Telegram\Types\Update;
  use \unreal4u\TelegramAPI\Telegram\Methods\SendMessage;
  use \unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
  use \unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Button;

  function createFromRango(int $rango, $args){
    switch($rango):
      case 100: return new Admin(...$args);
      case 50: return new Organizador(...$args);
      case 0: return new BaseUser(...$args);
    endswitch;
  }

  class BaseUser {
    public $id = 0;
    public $rango; /* Rango de la clase */
    // Comandos disponibles del usuario. Usado también en testing

    public function __construct($user, $tgLog, $logger, $db){
      $this->id = $user->id;
      $this->username = $user->username;
      $this->tgLog = $tgLog;
      $this->logger = $logger;
      $this->db = $db;
      $this->rango = 0;
      $this->user = $user;
      $this->available_commands = [
        "start" => "Inicia el bot",
        "help" => "Muestra esta ayuda",
        "lineas" => "Muestra las lineas disponibles"
      ];
      // tries to update last connection date
      $this->db->update_connection($this->id);

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

    final protected function sendSimpleMsg(string $text){
      $response = new SendMessage();
      $response->text = $text;
      $response->chat_id = $this->id;
      $this->logger->info("Sending message: ".json_encode($response));
      return $this->tgLog->performApiRequest($response);
    }

    public function exec(Update $update){
      // Update las connection
      // TODO: Test which error returns

      $command = trim($update->message->text, " \t\n\r\0\x0B/");
      $command = explode(" ", $command)[0];
      $this->logger->info("Executing command $command");
      switch($command):
        case "start": return $this->start($update);
        case "help": return $this->help($update);
        case "lineas": return $this->lineas($update);
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

    public function lineas(Update $update){
      $this->sendSimpleMsg("Lo siento, este método no está aún implementado :(");
    }
  }

  class Organizador extends BaseUser{
    public function __construct($user, $tgLog, $logger, $db){
      parent::__construct($user, $tgLog, $logger, $db);
      $this->rango = 50;
      $this->available_commands["menu"] = "Muestra el menú para abrir y cerrar lineas en #cxt18 (lo mismo el hashtag cambia)";
    }

    public function exec(Update $update){
      $command = trim($update->message->text, " \t\n\r\0\x0B/");
      $command = explode(" ", $command)[0];
      switch($command):
        case "menu": return $this->showMenu($update);
        default: return parent::exec($update);
      endswitch;
    }

    public function showMenu(Update $update){
      $this->logger->debug("Showing main menu");
      $kmarkup = new Markup();
      $i = 0;

      $alredy_shown = [];
      foreach($this->db->getLineas() as $linea){
        $button = new Button();
        if(!in_array($linea["phone_number"], $alredy_shown)){
          array_push($alredy_shown, $linea["phone_number"]);
          $button->text = sprintf("%s", $linea["phone_number"]);
          $button->callback_data = "nmr:".$linea["phone_number"];
          $kmarkup->inline_keyboard[$i][0] = $button;
          $i++;
        }
      }
      $this->logger->debug("Created markup " . var_export($kmarkup, true));

      $msg = new SendMessage();
      $msg->chat_id = $this->id;
      $msg->text = "Elige una línea que administrar: ";
      $msg->reply_markup = $kmarkup;

      $this->logger->debug("Sending message " . var_export($msg, true));

      return $this->tgLog->performApiRequest($msg);
    }

    public function lineas(Update $update){
      // Primero obtener las lineas, luego mostrarlas en formato
      // <id> <numero> <localizacion> <status> <tiempo abierta/cerrada>
      $text = "<id> <numero> <localizacion> <status> <last_update>\n";
      $format = "%d %s %s %s\n";
      foreach($this->db->getLineas() as $linea){
        $text .= sprintf($format, $linea["id"], $linea["phone_number"], $linea["location"], $linea["status"]);
        $this->logger->info(json_encode($linea));
      }

      $this->sendSimpleMsg($text);
    }
  }

  class Admin extends Organizador{
    public function __construct($user, $tgLog, $logger, $db){
      parent::__construct($user, $tgLog, $logger, $db);
      $this->rango = 100;
      $this->available_commands["lineas"] = "Muestra todas las lineas";
      $this->available_commands["su"] = "<rango> <comando> hace el comando emulando ser ese rango";
    }


    public function exec(Update $update){
      $command = trim($update->message->text, " \t\n\r\0\x0B/");
      $this->logger->debug(json_encode(explode(" ", $command)));
      $command = explode(" ", $command)[0];
      $this->logger->info("Executing command as admin $command");
      switch($command):
        case "su": return $this->su($update);
        default: return parent::exec($update);
      endswitch;
    }

    public function su(Update $update){
      $this->logger->info("/su execution");
      $arr = explode(" ", $update->message->text, 3);
      $user = createFromRango((int)$arr[1], [$this->user, $this->tgLog, $this->logger, $this->db]);
      $update->message->text = $arr[2];
      $user->exec($update);
    }
  }
