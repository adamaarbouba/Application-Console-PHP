<?php

require_once __DIR__ . "/../exception/ValidationException.php";
class Database {

    private $host = "db";
    private $dbName = "payment_system";
    private $username = "root";
    private $password = "12344321";


    private $conn;



    public function __construct()
    {
    
         try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbName;", $this->username, $this->password);
         } catch (\PDOException $th) {
            throw new ServerErrorException("Database Error", 500, $th);
         }
    }


    public function getConnection(){
        return $this->conn;
    }

}