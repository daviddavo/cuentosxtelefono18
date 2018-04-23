<?php
  require_once __DIR__.'/00.common.php';

  use \unreal4u\TelegramAPI\Telegram\Types\Message;
  use \unreal4u\TelegramAPI\Telegram\Types\CallbackQuery;
  use \unreal4u\TelegramAPI\Telegram\Methods\SendMessage;
  use \unreal4u\TelegramAPI\Telegram\Methods\EditMessageText;
  use \unreal4u\TelegramAPI\Telegram\Methods\AnswerCallbackQuery;
  use \unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
  use \unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Button;

  function createFromRango(int $rango, ...$args){
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
      $logger->debug("Creating user from: ". json_encode($user, true));
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

    // bool to status
    final public function b2s(bool $status){
      return $status ? "abierta":"cerrada";
    }
    // bool to verb
    final public function b2v(bool $status){
      return $status ? "abrir":"cerrar";
    }


    final protected function sendSimpleMsg(string $text){
      $response = new SendMessage();
      $response->text = $text;
      $response->chat_id = $this->id;
      $this->logger->info("Sending message: ".json_encode($response));
      return $this->tgLog->performApiRequest($response);
    }

    public function exec(Message $message){
      // Update las connection
      // TODO: Test which error returns

      $command = trim($message->text, " \t\n\r\0\x0B/");
      $command = explode(" ", $command)[0];
      $this->logger->info("Executing command $command");
      switch($command):
        case "start": return $this->start($message);
        case "help": return $this->help($message);
        case "lineas": return $this->lineas($message);
        default:
          $this->logger->info("Command {$command} not implemented, available commands: ".json_encode($this->available_commands));
          return false;
      endswitch;
    }

    final private function start(Message $message){
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

    final private function help(Message $message){
      $ran = $this->ranStr($this->rango);
      $helpText = "Eres {$ran} y tus comandos disponibles son:\n";
      foreach($this->available_commands as $command => $text){
        $helpText .= "/{$command}: {$text}\n";
      }
      return $this->sendSimpleMsg($helpText);
    }

    public function lineas(Message $message){
      $this->sendSimpleMsg("Lo siento, este método no está aún implementado :(");
    }
  }

  class Organizador extends BaseUser{
    public function __construct($user, $tgLog, $logger, $db){
      parent::__construct($user, $tgLog, $logger, $db);
      $this->logger->debug("Created organizador");
      $this->rango = 50;
      $this->available_commands["menu"] = "Muestra el menú para abrir y cerrar lineas en #cxt18 (lo mismo el hashtag cambia)";
      $this->available_commands["cerrarTodas"] = "Cierra TODAS las líneas. Así que ten cuidao";
    }

    public function exec(Message $message){
      $command = trim($message->text, " \t\n\r\0\x0B/");
      $command = explode(" ", $command)[0];
      switch($command):
        case "menu": return $this->showMenu($message);
        case "cerrarTodas": return $this->cerrarTodas($message);
        default: return parent::exec($message);
      endswitch;
    }

    private function getMenuMsg($msg){
      $kmarkup = new Markup();
      $i = 0;

      $alredy_shown = [];
      foreach($this->db->getLineas() as $linea){
        $button = new Button();
        if(!in_array($linea["phone_number"], $alredy_shown)){
          array_push($alredy_shown, $linea["phone_number"]);
          $button->text = sprintf("%s", $linea["phone_number"]);
          $button->callback_data = "select_number:".$linea["phone_number"];
          $kmarkup->inline_keyboard[$i][0] = $button;
          $i++;
        }
      }
      $this->logger->debug("Created markup " . var_export($kmarkup, true));

      $msg->chat_id = $this->id;
      $msg->text = "Elige una línea que administrar: ";
      $msg->reply_markup = $kmarkup;

      $this->logger->debug("Created msg " . var_export($msg, true));
    }

    public function showMenu(Message $message){
      $this->logger->debug("Showing main menu");

      $msg = new SendMessage();
      $this->getMenuMsg($msg);

      $this->logger->debug("Sending message " . var_export($msg, true));

      return $this->tgLog->performApiRequest($msg);
    }

    protected function cerrarTodas(Message $message){
      // Called to close ALL linesç
      foreach($this->db->getLineas() as $linea){
        $this->db->setLineStatus($linea["id"], false);
      }
      $this->sendSimpleMsg("Cerradas todas las líneas");
    }

    public function lineas(Message $message){
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

    public function callback_query(CallbackQuery $callBack){
      $command = explode(":", $callBack->data, 2);
      switch($command[0]):
        case "select_number": return $this->select_number($callBack);
        case "open_line": return $this->open_line($callBack);
        case "close_line": return $this->close_line($callBack);
        case "update_msg": return $this->update_msg($callBack);
        case "goto": return $this->goToMenu($callBack);
        default: $this->logger->info("Callback query method " . $callBack->data . " not implemented");
      endswitch;
    }

    protected function goToMenu(CallbackQuery $callBack){
      $this->logger->debug("Returning to main menu");
      $msg = new EditMessageText();
      $this->getMenuMsg($msg);
      $this->logger->debug(var_export($msg, true));
      $msg->message_id = $callBack->message->message_id;

      return $this->tgLog->performApiRequest($msg);
    }

    protected function select_number(CallbackQuery $callBack){
      // Now we have to 'edit' the message and show the different lineas
      // Associated with that number and close/open (and an update button)
      $numero = explode(":", $callBack->data)[1];
      $lineas = $this->db->getLineasWhere($numero);
      $msg = new EditMessageText();
      $msg->chat_id = $this->id;
      $msg->message_id = $callBack->message->message_id;
      $msg->text = "Editando $numero";
      $msg->reply_markup = new Markup();
      foreach($lineas as $line){
          $button = new Button();
          $button->text = ($line["status"] ? "Cerrar " : "Abrir ") . $line["location"];
          $button->callback_data = ($line["status"] ? "close_line:" : "open_line:") . $line["id"];

          array_push($msg->reply_markup->inline_keyboard, [$button]);
      }
      $update_button = new Button();
      $update_button->text = "Actualizar info";
      $update_button->callback_data = "update_msg:$numero";
      $back_button = new Button();
      $back_button->text = "Atrás";
      $back_button->callback_data = "goto:menu";
      array_push($msg->reply_markup->inline_keyboard, [$back_button, $update_button]);

      // Tries to update, if it fails because the message is the same, then does nothing
      return $this->tgLog->performApiRequest($msg);
    }

    protected function open_line(CallbackQuery $callBack){
      $id = explode(":", $callBack->data)[1];
      $this->db->setLineStatus($id, true);
      return $this->update_msg($callBack);
    }

    protected function close_line(CallbackQuery $callBack){
      $id = explode(":", $callBack->data)[1];
      $this->db->setLineStatus($id, false);
      return $this->update_msg($callBack);
    }

    protected function update_msg(CallbackQuery $callBack){
      // Tries to update, if it fails because the message is the same, then does nothing
      $tmp = explode(" ", $callBack->message->text);

      $response = new AnswerCallBackQuery();
      $response->text = "Info actualizada";
      $response->callback_query_id = $callBack->id;

      $callBack->data = ":".end($tmp);
      $this->select_number($callBack);

      // And now send the response
      $this->tgLog->performApiRequest($response);
    }
  }

  class Admin extends Organizador{
    public function __construct($user, $tgLog, $logger, $db){
      parent::__construct($user, $tgLog, $logger, $db);
      $this->logger->debug("Created admin");
      $this->rango = 100;
      $this->available_commands["lineas"] = "Muestra todas las lineas";
      $this->available_commands["su"] = "<rango> <comando> hace el comando emulando ser ese rango";
      $this->available_commands["addOrganizador"] = "<@usuario> Añade un organizador a la BD"
    }


    public function exec(Message $message){
      $command = trim($message->text, " \t\n\r\0\x0B/");
      $this->logger->debug(json_encode(explode(" ", $command)));
      $command = explode(" ", $command)[0];
      $this->logger->info("Executing command as admin $command");
      switch($command):
        case "su": return $this->su($message);
        case "addOrganizador": return $this->addOrganizador($message);
        default: return parent::exec($message);
      endswitch;
    }

    public function addOrganizador(Message $message){
      $this->logger->info("Añadiendo usuario a la BD");
      $arr = explode(" ", message->text, 2);
      $this->db->update_rango($arr[1], 50);
    }

    public function su(Message $message){
      $this->logger->info("/su execution");
      $arr = explode(" ", $message->text, 3);
      $user = createFromRango((int)$arr[1], ...[$this->user, $this->tgLog, $this->logger, $this->db]);
      $message->text = $arr[2];
      $user->exec($message);
    }
  }
