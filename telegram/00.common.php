<?php
  require_once __DIR__.'/../vendor/autoload.php';

  use Monolog\Logger;
  use Monolog\Handler\StreamHandler;

  $log = new Logger('anything');
  $log->pushHandler(new StreamHandler(__DIR__."/../phplog.log", Logger::DEBUG));

  include __DIR__.'/config.php';
  $log->debug("Loaded 00.common.php");
