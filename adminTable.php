<?php
require __DIR__.'/telegram/00.common.php';
include __DIR__. "/telegram/db.php";

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

if(filter_input(INPUT_GET, "admin", FILTER_SANITIZE_STRING) == ADMIN_LANDING_TOKEN){
  $logger = new Logger('cxtbot');
  $logger->pushHandler(new RotatingFileHandler(__DIR__ . "/logs/phplog.log", 30, Logger::DEBUG));
  $db = new database(DB_SERVER, DB_USER, DB_PASSWORD, $logger);

  echo <<<'EOT'
  <table class='table table-dark text-center'>
  <thead><tr>
  <th>ID</th>
  <th>Teléfono</th>
  <th>Ubicación</th>
  <th>Estado</th>
  <th>Tiempo abierta/cerrada</th>
  </tr></thead><tbody>
EOT;

  foreach($db->getLineas() as $linea){
    $bg_color = $linea["status"]? "bg-success":"bg-danger";
    $then = $linea[($linea["status"]==="1")?"last_close":"last_open"];
    $t = floor(time() - strtotime($then) + 3600); // Timezone +1
    $date = sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);

    echo "<tr class='{$bg_color}'>";
    echo "<td>", $linea["id"], "</td>";
    echo "<td class='font-weight-bold'>", $linea["phone_number"], "</td>";
    echo "<td>", $linea["location"], "</td>";
    echo "<td>", $linea["status"]? "Esperando":"Contando", "</td>";
    echo "<td>$date</td>";
  }

  echo "</tbody></table>";
}else{
  echo "No has introducido bien la contraseña de adminsitrador :(";
  echo "Contraseña introducida: " . filter_input(INPUT_GET, "admin", FILTER_SANITIZE_STRING);
}
