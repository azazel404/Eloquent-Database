<?php

class database{

  /*property untuk untuk function singletorn
    conn untuk koneksi , table untuk manggil nama table , kolom untuk nama kolom ,
    stmt = statement buat prepared , attr adalah attribute buat prepared , param untuk
    bind param , prevdata , untuk attribute , pemanggilan array attribute
  */
  private static $instance;
  private $conn , $table , $column = "*" , $query , $stmt ,$attr , $param = [] , $prevdata = [];
  private $server = "localhost",
          $user   = "",
          $pass   = "",
          $dbname = "";

  public function __construct(){
    try {
      $this->conn = new PDO("mysql:host=$this->server;dbname=$this->dbname", $this->user , $this->pass);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION);
    } catch (PDOexception $e) {
      die($e->getMessage);
    }
  }

  public static function getInstance(){
      if (!isset (self::$instance)) {
        self::$instance = new database();
      }
      return self::$instance;
    }

  public function __clone(){
    return false;
  }

  //untuk mengeset table yang ingin dipilih
  public function setTable($table){
    $this->table = $table;
    return $this;
  }

  //untuk memilih data
  public function selectData($column = "*"){
    $this->query = "SELECT $column FROM $this->table";
    $this->column = $column;
    return $this;
  }

    //untuk menampilkan semua data
  public function all(){
    $this->run();
    return $this->stmt->fetchall(PDO::FETCH_OBJ);
  }
  //untuk menampilkan 1 data

  public function first(){
    $this->run();
    return $this->stmt->fetch(PDO::FETCH_OBJ);
  }

  //fungsi untuk penyambung query dengan AND
  public function where($cols , $sign , $data , $bridge = ' AND '){

    if (count($this->prevdata) == 0) {
      $bridge = "";
    }
    $this->query = "SELECT $this->column FROM $this->table WHERE";
    $this->prevdata[] = array(
        "cols" => $cols,
        "sign" => $sign,
        "data" => $data,
        "bridge" => $bridge
    );
    $this->getWhere($bridge);
    return $this;
  }

//fungsi untuk penyambung query dengan OR
  public function orWhere($cols ,$sign ,$data){
    $this->where($cols , $sign , $data ,$bridge = " OR ");
    return $this;
  }
  //fungsi untuk eksekusi where AND dan WHERE OR
  //fungsi untuk eksekusi where AND dan WHERE OR
  public function getWhere($bridge){
    if (count($this->prevdata) > 1) {
      $this->attr = '';
      $this->param = [];
    }
    $x = 1;
    foreach ($this->prevdata as $value) {

      if ($x <= count($this->prevdata)) {
      $this->attr .= $value['bridge'];
        }

      $this->attr .= $value['cols'] . " ".$value['sign'] . ' ?';
      $this->param[] = $value['data'];

    $x++;
    }
    return $this;
  }

//fungsi untuk membuat data
  public function createData($fields = array()){
    $columns = implode(" ,",array_keys($fields));
    $value = "";
    $x = 1;
    foreach ($fields as $field) {
      $this->param[] = $field;
      $value .= '?';
            if ($x < count($fields)) {
              $value .= ', ';
            }
      $x++;
    }
    $this->query = "INSERT INTO $this->table($columns) VALUES ($value)";
    $this->run();
  }

  //fungsi untuk update
  public function update($fields = array()){
    $col = '';
    $x = 1;
    $count = count($this->param);
    foreach ($fields as $key => $value) {
      $this->param[] = $value;
      $col .= $key .'=?';

        if ($x < count($fields)) {
          $col .= ', ';
        }
        $x++;
    }
    //memindahkan array yang dipertama menjadi diakhir
    for ($i=0; $i < count($count) ; $i++) {
      $this->param[] = array_shift($this->param);
    }

    $this->query = "UPDATE $this->table SET $col WHERE";
    $this->run();
  }

  //fungsi untuk menghapus database
  public function delete(){
    $this->query = "DELETE FROM $this->table WHERE";
    $this->run();
  }

  //fungsi untuk menyusun hasil dari yang kita tampilkan berdasarkan tampilan tanya
  public function OrderBy($col = "id" ,$type){
    $this->attr .= " ORDER BY $col $type";
    return $this;
  }

  //fungsi untuk melimit data yang ditampilkan
  public function LimitID($number){
    $this->attr .= "LIMIT $number";
    return $this;
  }

  public function flush(){
    $this->prevdata = "";
    $this->param = [];
    $this->query = "";
    $this->attr = "";
  }



  //function untuk menjalankan perintah query
  public function run(){
    try {
      // var_dump($this->param);
      die($this->query ." ". $this->attr);
      $this->stmt = $this->conn->prepare($this->query ." ". $this->attr);
      $this->stmt->execute($this->param);
      $this->flush();
    } catch (PDOexception $e) {
      die($e->getMessage());
    }
  }




}

 ?>
