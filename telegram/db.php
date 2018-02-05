<?php
require_once __DIR__.'/00.common.php';

// Esto es un enum
abstract class rango
{
  const User = 0;
  const Admin = 100;
}

class database{
  protected $conn;
  protected $logger;

  public function __construct($servername, $username, $password, $logger){
    $this->conn = new mysqli($servername, $username, $password, DB_DATABASE);
    $this->logger = $logger;

    if($this->conn->connect_error){
      die("Connection failed: " . $this->conn->connect_error);
    }
  }

  private function query(string $query){
    $res = $this->conn->query($query);
    if($res === true){
    }else if ($res->num_rows == 1){
      $res = $res->fetch_assoc();
    }else if ($res->num_rows > 1){
      $res = $res->fetch_all(MYSQLI_ASSOC);
    }else{
      $this->logger->error("SQL Error: ". $query . "\n" . $this->conn->error);
      return false;
    }

    $this->logger->info("Performed query: ". $query . "\n Response: " . json_encode($res,true));
    return $res;
  }

  public function insertUser($id, $username){
    $query = "INSERT INTO " . DB_USERS_TABLE . " (user_id, username) VALUES ({$id}, '{$username}')";
    $this->query($query);
  }

  public function userExists($id){
    $query = "SELECT * FROM `".DB_USERS_TABLE."` WHERE user_id={$id}";
    $res = $this->query($query);
    $this->logger->debug("Query response: " . var_export($res, true));
    return $res != NULL;
  }

  public function getUser($id){
    $query = "SELECT * FROM `".DB_USERS_TABLE."` WHERE user_id={$id}";
    $res = $this->query($query);
    return $res;
  }

  public function update_connection($id){
    $query = "UPDATE `".DB_USERS_TABLE."` SET last_connection=CURRENT_TIMESTAMP WHERE user_id={$id}";
    $this->query($query);
  }

  public function getLineas(){
    return $this->query("SELECT * FROM `".DB_LINES_TABLE."`");
  }

  public function close(){ $this->conn->close();}
}
