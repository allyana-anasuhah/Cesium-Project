<?php
/*
* DB Connection using PDO
*
*/
class dbconnect extends PDO
{
   protected static $instance;
   protected static $options = array();
   private $lastInsID;
   private $lastStatement;
 
    // construct class based on PDO __construct
   public function __construct($o = false)
   {
      if (!array_key_exists('dsn', $o) || !array_key_exists('username', $o) || !array_key_exists('pass', $o)) {
        $this->printErr('Connection array(dsn,username,pass) not set properly!');
        die();
      }

      $dsn = $o['dsn'];
      $username = $o['username'];
      $pass = $o['pass'];
      try {
          parent::__construct($dsn, $username, $pass);
          parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      catch(PDOException $e){
        $this->printErr("Connection failed: " . $e->getMessage());
      }
   }

   public function query($query){
      $result = parent::query($query);
      return($result);
   }

   // return db instance
    public static function getInstance() {
        if( !self::$instance ) {
            self::$instance = new self(); 
        }
        return self::$instance;
    }

    public function fetchAll($sql, $param = array(), $fetchMode = 'FETCH_ASSOC'){
      $statement = parent::prepare($sql);
      try {
          @$statement->execute($param);
      }catch(PDOException $e){
          $this->printErr("SQL_ERROR :" . $e->getMessage());
          return false;
      }
      $fm = $this->getFetchMode($fetchMode);
      $data = $statement->fetchAll($fm);
      return $data;  
    }

    public function fetchCol($sql, $param = array()){
      return $this->fetchAll($sql, $param, 'FETCH_COLUMN');
    }

    public function fetchOne($sql, $param = array()){
      $statement = parent::prepare($sql);
      // bindparam
      try {
          @$statement->execute($param);
      }catch(PDOException $e){
          $this->printErr("SQL_ERROR :" . $e->getMessage());
          return false;
      }
      $data = $statement->fetchColumn(0);
      return $data;  
    }

    public function fetchRow($sql, $param = array(), $fetchMode = 'FETCH_ASSOC'){
      $statement = parent::prepare($sql);
      try {
          @$statement->execute($param);
      }
      catch(PDOException $e){
          $this->printErr("SQL_ERROR :" . $e->getMessage());
          return false;
      }
      $fm = $this->getFetchMode($fetchMode);
      $data = $statement->fetch($fm);
      return $data; 
    }

    public function execute($sql, $var_array = array()){
      $this->lastStatement = $statement = parent::prepare($sql);
      $ret = $statement->execute($var_array);
      $this->lastInsID = $this->lastInsertId();
       $err = $statement->errorInfo();
       if ($err[2]) {
         $this->printErr($err[2]);
         return false;
       }
      return $ret;
    }

    private function getFetchMode($fetchMode){
      switch ($fetchMode) {
        case 'FETCH_BOTH ':
          $fm = PDO::FETCH_BOTH ;
          break;
        case 'FETCH_NUM':
          $fm = PDO::FETCH_NUM;
          break;
        case 'FETCH_COLUMN':
          $fm = PDO::FETCH_COLUMN;
          break;
        case 'FETCH_ASSOC':
        default:
          $fm = PDO::FETCH_ASSOC;
          break;
      }
      return $fm;
    }

    // return last insert id set in execute function
    public function getLastInsertID(){
      return $this->lastInsID;
    }

    public function printErr($msg){
      echo '<div style="color:red;">'.$msg.'</div>';
    }

    public function lastRowCount(){
      return $this->lastStatement->rowCount();
    }

    public function dbug($var, $die = false){
      echo '<pre>';
      print_r($var);
      echo '<br>';
      if($die) die('debugging');
    }
}



