<?php

include_once "./entity/Payment.php";

class Virement extends Payment
{

    private $rib;

    public function __construct($montant, $rib)
    {
        parent::__construct($montant);
        $this->rib = $rib;
    }

    public function __get($property)
    {
        return $this->$property;
    }

    public function pay() {
        $this->status = self::PAID;
        $this->commande->setStatus(Commande::STATUS_PAYE);
    }
}
