<?php
require_once __DIR__.'/../telegram/00.common.php';

include "../telegram/db.php";

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

// TODO: Hacer una cache para que no se flodee la db

$logger = new Logger('cxtbot');
$logger->pushHandler(new RotatingFileHandler(__DIR__ . "/../logs/phplog.log", 30, Logger::DEBUG));

$db = new database(DB_SERVER, DB_USER, DB_PASSWORD, $logger);
$userLines = array();
// Por cada linea...
foreach ($db->getLineas() as $linea) {
  // Buscamos si dicha linea está en el array
  $found = false;
  foreach($userLines as $key => $uLinea){
    // Si la encuentra...
    if($uLinea["phone_number"] == $linea["phone_number"]){
      $logger->info("Line found: " . json_encode($linea). "; " . json_encode($uLinea));
      $userLines[$key]["total"]++;
      $userLines[$key]["available"] += $linea["status"];
      $logger->info("Line found: " . json_encode($linea). "; " . json_encode($uLinea));
      $found = true;
      break;
    }
  }
  // Si no está, se añade...
  if(!$found){
    $uLinea = [
      "phone_number"=>$linea["phone_number"],
      "available"=>intval($linea["status"]),
      "total"=>1, // total and times.length() should be the same
      "times"=>[
        $linea[($linea["status"] == "1")?"last_open":"last_close"],
      ]
    ];
    array_push($userLines, $uLinea);
  }
}

echo json_encode($userLines);
?>
