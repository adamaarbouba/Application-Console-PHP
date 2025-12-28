<?php



class Commande
{

    private $id;
    private $montantTotal;
    private $status;
    private $client;


    public const STATUS_EN_ATTENTE  = "Pending";
    public const STATUS_ANNULE  = "Cancel";
    public const STATUS_PAYE  = "Paid";



    public function __construct($montantTotal, $status = self::STATUS_EN_ATTENTE)
    {
        $this->montantTotal = $montantTotal;
        $this->status  = $status;
    }


    public function __get($property)
    {
        return $this->$property;
    }




    public function setId($id)
    {

        if (!is_numeric($id) || (int) $id <= 0) {
            throw new ValidationException("ID doit etre un entier positif");
        }

        $this->id = (int) $id;
    }


     public function setClient($client)
    {

        if (!($client instanceof Client) && is_null($client->id) ) {
            throw new ValidationException("l'objet client passé à la commande non valide!!");
        }

        $this->client =$client;
    }

    public function setStatus($status)
    {
        $status_array = [self::STATUS_ANNULE, self::STATUS_EN_ATTENTE, self::STATUS_PAYE];

        if (!in_array($status, $status_array)) {

            throw new ValidationException("status doit etre dans la plage suivante: " . self::STATUS_ANNULE . "," . self::STATUS_EN_ATTENTE . "," .  self::STATUS_PAYE);
        }

        $this->status = $status;
    }
}
