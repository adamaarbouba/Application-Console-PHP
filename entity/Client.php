<?php



class Client {

    private $id;
    private $name;
    private $email;


    public function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
    }
    

    public function __get($property)
    {
        // $property = "email" return $this->email
        return $this->$property;   // $property = "name" return $this->name
    }


    

    public function setId($id){

        if(!is_numeric($id) || (int) $id <= 0){
            throw new ValidationException("ID doit etre un entier positif");
        }

        $this->id = (int) $id;
    }
}