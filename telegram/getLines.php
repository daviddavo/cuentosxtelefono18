<?php
require_once __DIR__.'/00.common.php';

include "db.php";

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

// TODO: Hacer una cache para que no se flodee la db

$logger = new Logger('cxtbot');
$logger->pushHandler(new RotatingFileHandler(__DIR__ . "/../logs/phplog.log", 30, Logger::DEBUG));

$db = new database(DB_SERVER, DB_USER, DB_PASSWORD, $logger);
echo json_encode($db->getLineas());
?>
