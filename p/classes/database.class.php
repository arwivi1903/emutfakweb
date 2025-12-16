<?php

// Session başlatılmamışsa başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//namespace bszo\db;

class Database 
{
  private $MYSQL_HOST='localhost'; 
  private $MYSQL_USER='root'; // mysql kullanıcı adınız  
  private $MYSQL_PASS='';  // mysql şifreniz
  public  $MYSQL_DB='emutfak_master'; //kendi database adınızı yazın
  private $CHARSET='utf8mb4';
  private $COLLATION='utf8mb4_unicode_ci';
  private $pdo=null;
  private $stmt=null;
  private $lastError=null;
  private $debugMode=false;
  private $inTransaction=false;

  private function ConnectDB(){
    //database bağlantısı
    $SQL="mysql:host=".$this->MYSQL_HOST.";dbname=".$this->MYSQL_DB.";charset=".$this->CHARSET; 
    try{
      $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        \PDO::ATTR_TIMEOUT => 30,
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '".$this->CHARSET."' COLLATE '".$this->COLLATION."'"
      ];
      $this->pdo = new \PDO($SQL, $this->MYSQL_USER, $this->MYSQL_PASS, $options);
    }catch(PDOException $e){
      $this->logError("Veritabanı bağlantı hatası: " . $e->getMessage());
      throw new Exception("Veritabanı bağlantısı başarısız. Sistem yöneticisine başvurunuz.");
    }
  }

  public function __construct($dbName = null){ 
    //bağlantıyı aç - multi-tenant desteği
    if ($dbName === 'master') {
      $this->MYSQL_DB = 'emutfak_master';
    } elseif ($dbName !== null) {
      $this->MYSQL_DB = $dbName;
    } elseif (isset($_SESSION['database_name'])) {
      $this->MYSQL_DB = $_SESSION['database_name'];
    }
    $this->ConnectDB();
  }
  
  private function myQuery($query,$params=null){
    //diğer metodlardaki tekrarlı verileri bitirmek için kullanılan metod
    try{
      if(is_null($params)){
        $this->stmt=$this->pdo->query($query);
      }else{
        $this->stmt=$this->pdo->prepare($query);
        $this->stmt->execute($params);
      }
      return $this->stmt;
    }catch(PDOException $e){
      $this->logError("Query hatası: " . $query . " - " . $e->getMessage());
      throw new Exception("Veritabanı sorgusu başarısız.");
    }
  }

  public function getRows($query,$params=null){
    //çoklu satır verilerini çekmek için
    try{
      return $this->myQuery($query,$params)->fetchAll();
    }catch(Exception $e){
      $this->logError($e->getMessage());
      return [];
    }
  }

  public function getRowCount($query,$params=null){
    //count cekme
    try{
      return $this->myQuery($query,$params)->rowCount();
    }catch(Exception $e){
      $this->logError($e->getMessage());
      return 0;
    }
  }

  
  public function getRow($query,$params=null){
    //tek satır veri çekmek  için
    try{
      return $this->myQuery($query,$params)->fetch();
    }catch(Exception $e){
      $this->logError($e->getMessage());
      return null;
    }
  }

  public function getColumn($query,$params=null){
    //tek satırın sütun verisini çekmek için nokta veri alışı
    try{
      return $this->myQuery($query,$params)->fetchColumn();
    }catch(Exception $e){
      $this->logError($e->getMessage());
      return null;
    }
  }

  public function Insert($query,$params=null){
    //kayıt eklemek için
    try{
      $this->myQuery($query,$params);
      return $this->pdo->lastInsertId();
    }catch(Exception $e){
      $this->logError($e->getMessage());
      return false;
    }
  }

  public function Update($query,$params=null){
    //kayıt güncellemek için
    try{
      return $this->myQuery($query,$params)->rowCount();
    }catch(Exception $e){
      $this->logError($e->getMessage());
      return 0;
    }
  }

  public function Delete($query,$params=null){
    //kayıt Silmek için
    return $this->Update($query,$params);
  }

  public function Limit($query,$p1=1,$p2=null){
    //limit kayıtlarını pdo ile çekmek için
    try{
      $this->stmt=$this->pdo->prepare($query);
      $this->stmt->bindValue(1, $p1, \PDO::PARAM_INT);
      if(!is_null($p2))
        $this->stmt->bindValue(2, $p2, \PDO::PARAM_INT);
      $this->stmt->execute();
      return $this->stmt->fetchAll();
    }catch(Exception $e){
      $this->logError($e->getMessage());
      return [];
    }
  }
  public function __destruct(){
    //bağlantıyı kapat
    $this->pdo=NULL;
  }

  public function CreateDB($query){ 
    //veritabanı oluşturmak için
    try{
      $myDB=$this->pdo->query($query.' CHARACTER SET '.$this->CHARSET.' COLLATE '.$this->COLLATION);
      return $myDB;
    }catch(Exception $e){
      $this->logError($e->getMessage());
      return false;
    }
  }

  public function TableOperations($query){ 
    //tablo operasyonları için
    try{
      $myTable=$this->pdo->query($query);
      return $myTable;
    }catch(Exception $e){
      $this->logError($e->getMessage());
      return false;
    }
  }

  public function Maintenance(){ 
    //tabloların bakımı için
    try{
      $myTable=$this->pdo->query("SHOW TABLES");
      $myTable->setFetchMode(\PDO::FETCH_NUM);
      $results = [];
      if($myTable){
        foreach($myTable as $items){ 
          $tableName = htmlspecialchars($items[0], ENT_QUOTES, 'utf-8');
          $check=$this->pdo->query("CHECK TABLE `".$tableName."`");
          $analyze=$this->pdo->query("ANALYZE TABLE `".$tableName."`");
          $repair=$this->pdo->query("REPAIR TABLE `".$tableName."`");
          $optimize=$this->pdo->query("OPTIMIZE TABLE `".$tableName."`");
          if($check && $analyze && $repair && $optimize){
            $results[] = "✓ " . $tableName . " bakımı yapıldı";
          }else{
            $results[] = "✗ " . $tableName . " bakımında hata oluştu";
          }
        }
      }
      return $results;
    }catch(Exception $e){
      $this->logError($e->getMessage());
      return [];
    }
  }

  // ===== YENİ FONKSIYONLAR =====

  private function logError($message){
    //hata logging
    $this->lastError = $message;
    if($this->debugMode){
      error_log("[DATABASE] " . date("Y-m-d H:i:s") . " - " . $message);
    }
  }

  public function setDebugMode($debug = false){
    //debug modunu aç/kapat
    $this->debugMode = $debug;
    return $this;
  }

  public function getLastError(){
    //son hatayı getir
    return $this->lastError;
  }

  public function beginTransaction(){
    //işlem başlat
    try{
      $this->pdo->beginTransaction();
      $this->inTransaction = true;
      return true;
    }catch(Exception $e){
      $this->logError("Transaction başlatma hatası: " . $e->getMessage());
      return false;
    }
  }

  public function commit(){
    //işlemi kaydet
    try{
      $this->pdo->commit();
      $this->inTransaction = false;
      return true;
    }catch(Exception $e){
      $this->logError("Commit hatası: " . $e->getMessage());
      return false;
    }
  }

  public function rollback(){
    //işlemi geri al
    try{
      $this->pdo->rollBack();
      $this->inTransaction = false;
      return true;
    }catch(Exception $e){
      $this->logError("Rollback hatası: " . $e->getMessage());
      return false;
    }
  }

  public function tableExists($tableName){
    //tablo var mı kontrolü
    try{
      $query = "SHOW TABLES LIKE ?";
      $stmt = $this->pdo->prepare($query);
      $stmt->execute([$tableName]);
      return $stmt->rowCount() > 0;
    }catch(Exception $e){
      $this->logError("Table exists kontrolü hatası: " . $e->getMessage());
      return false;
    }
  }

  public function insertBatch($table, $columns, $data){
    //toplu insert işlemi
    if(empty($data)) return 0;
    try{
      $placeholders = "(" . implode(",", array_fill(0, count($columns), "?")) . ")";
      $query = "INSERT INTO `" . htmlspecialchars($table, ENT_QUOTES, 'utf-8') . "` (" . implode(",", $columns) . ") VALUES " . implode(",", array_fill(0, count($data), $placeholders));
      $params = [];
      foreach($data as $row){
        $params = array_merge($params, array_values($row));
      }
      return $this->Update($query, $params);
    }catch(Exception $e){
      $this->logError("Batch insert hatası: " . $e->getMessage());
      return 0;
    }
  }

  public function getRowsAssoc($query, $params=null){
    //çoklu satır verilerini array olarak çekmek için
    try{
      $stmt = $this->pdo->prepare($query);
      if(!is_null($params)){
        $stmt->execute($params);
      }else{
        $stmt->execute();
      }
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }catch(Exception $e){
      $this->logError($e->getMessage());
      return [];
    }
  }

  public function getRowAssoc($query, $params=null){
    //tek satır verisini array olarak çekmek için
    try{
      $stmt = $this->pdo->prepare($query);
      if(!is_null($params)){
        $stmt->execute($params);
      }else{
        $stmt->execute();
      }
      return $stmt->fetch(\PDO::FETCH_ASSOC);
    }catch(Exception $e){
      $this->logError($e->getMessage());
      return null;
    }
  }

  public function inTransaction(){
    //transaction durumunu kontrol et
    return $this->inTransaction;
  }

  // ===== ALIAS METODLAR (Geriye uyumluluk için) =====
  
  public function allAssoc($query, $params=null){
    //alias: getRowsAssoc için (error handling ile)
    if (!is_string($query) || trim($query) === '') {
      $this->logError("Invalid query in allAssoc");
      return [];
    }
    return $this->getRowsAssoc($query, $params);
  }

  public function rowAssoc($query, $params=null){
    //alias: getRowAssoc için
    return $this->getRowAssoc($query, $params);
  }

}	
?>