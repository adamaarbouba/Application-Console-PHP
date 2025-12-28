<?php

abstract class Payment
{


    public const UNPAID = "Unpaid";
    public const PAID = "Paid";


    protected $id;
    protected $date;
    protected $status;
    protected $montant;
    protected $commande;


    public function __construct($montant)
    {
        $this->montant = $montant;
        $this->status = self::UNPAID;
    }
    abstract public function pay();


    public function setId($id)
    {

        if (!is_numeric($id) || (int) $id <= 0) {
            throw new ValidationException("ID doit etre un entier positif");
        }

        $this->id = (int) $id;
    }


    public function setCommande($commande)
    {

        if (!($commande instanceof Commande) && is_null($commande->id)) {
            throw new ValidationException("l'objet commande passé au payment non valide!!");
        }

        $this->commande = $commande;
    }

    public function setStatus($status)
    {
        $status_array = [self::PAID, self::UNPAID];

        if (!in_array($status, $status_array)) {

            throw new ValidationException("status doit etre dans la plage suivante: " . self::UNPAID . "," . self::PAID);
        }

        $this->status = $status;
    }

    public function getMontant()
    {
        return $this->montant;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function getCommande()
    {
        return $this->commande;
    }
    public function getId()
    {
        return $this->id;
    }
}
