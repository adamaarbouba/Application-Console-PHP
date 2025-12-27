<?php

require_once "./exception/ValidationException.php";
class Database {

    private $host = "localhost";
    private $dbName = "syspaiement";
    private $username = "root";
    private $password = "";


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