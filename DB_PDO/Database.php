<?php

class Database
{
    /*
    * add database credentials
    */
    private $server = 'localhost',
            $user   = 'root',
            $pass   = 'root',
            $dbName = 'pdo_tuts';

    private static $_instance = null;

    private $_conn, $_table, $_columns = '*', $_query, $_statement, $_attr,
            $_params = [], $_prevData = [];

    //---- construct
    public function __construct()
    {
      try {
        $this->_conn = new PDO("mysql:host=$this->server;dbname=$this->dbName", $this->user, $this->pass);
        $this->_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }catch (PDOException $e){
        die($e->getMessage());
      }
    }

    //get database, singleton pattern
    public static function getInstance()
    {
      if (!isset(self::$_instance)) {
        self::$_instance = new Database();
      }

      return self::$_instance;
    }

    //prevent from clone
    public function __clone()
    {
      return false;
    }

    //set table to used
    public function setTable($table)
    {
      $this->_table = $table;
      return $this;
    }

    //set which columns
    public function select($columns = '*')
    {
      $this->_query   = "SELECT $columns FROM $this->_table";
      $this->_columns = $columns;
      return $this;
    }

    public function all()
    {
      $this->run();
      return $this->_statement->fetchAll(PDO::FETCH_OBJ);
    }

    public function first()
    {
      $this->run();
      return $this->_statement->fetch(PDO::FETCH_OBJ);
    }

    public function run()
    {
      var_dump($this->_params);
      die($this->_query . ' '. $this->_attr);

      try {
        $this->_statement = $this->_conn->prepare($this->_query . ' '. $this->_attr);
        $this->_statement->execute($this->_params);
        $this->flush();
      } catch (Exception $e) {
        die($e->getMessage());
      }
    }

    public function where($col, $sign, $value, $bridge = ' AND ')
    {
      $this->_query       = "SELECT $this->_columns FROM $this->_table WHERE";

      //first where method
      if (count($this->_prevData) == 0) {
        $bridge = '';
      }

      $this->_prevData[]  = array(
                              'col'    => $col,
                              'sign'   => $sign,
                              'value'  => $value,
                              'bridge' => $bridge,
                            );

      $this->getWhere($bridge);
      return $this;
    }

    public function orWhere($col, $sign, $value)
    {
      $this->where($col, $sign, $value, $bridge = ' OR ');
      return $this;
    }

    public function getWhere($bridge)
    {
      //clear multiple where
      if (count($this->_prevData) > 1) {
          $this->_attr   = '';
          $this->_params = [];
      }

      $x = 1;
      foreach ($this->_prevData as $prev) {

        if ($x <= count($this->_prevData)) {
          $this->_attr .= $prev['bridge'];
        }

        $this->_attr    .=  $prev['col'] . ' ' .$prev['sign'] . ' ?';
        $this->_params[] = $prev['value'];

        $x++;
      }

      return $this;
    }

    //insert data
    public function create($fields = array())
    {
      $cols   = implode(", ", array_keys($fields));
      $values = '';
      $x      = 1;

      foreach ($fields as $field) {
        $this->_params[] = $field;
        $values .= '?';

        if ($x < count($fields)) {
          $values .= ', ';
        }
        $x++;
      }

      $this->_query = "INSERT INTO $this->_table($cols) VALUES ($values)";
      $this->run();
    }

    //update data
    public function update($fields = array())
    {
      $cols = '';
      $x    = 1;

      $total_prev = count($this->_params);

      foreach ($fields as $key => $value) {
        $this->_params[] = $value;
        $cols .= $key .'=?';

        if ($x < count($fields)) {
          $cols .= ', ';
        }
        $x++;
      }

      for ($i=0; $i<$total_prev; $i++) {
        $this->_params[] = array_shift($this->_params);
      }

      $this->_query = "UPDATE $this->_table SET $cols WHERE";
      $this->run();
    }

    //menghapus data
    public function delete()
    {
      $this->_query = "DELETE FROM $this->_table WHERE";
      $this->run();
    }

    public function orderBy($col = 'id', $type)
    {
      $this->_attr .= " ORDER BY $col $type";
      return $this;
    }

    //limit query
    public function take($num)
    {
      $this->_attr .= " LIMIT $num";
      return $this;
    }

    public function flush()
    {
      $this->_attr   = '';
      $this->_query  = '';
      $this->_params = [];
      $this->_prevData = [];
    }



}
