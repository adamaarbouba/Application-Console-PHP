<?php

include_once "./entity/Payment.php";


class Carte extends Payment
{

    private $creditCardNumber;

    public function __construct($montant, $creditCardNumber)
    {
        parent::__construct($montant);
        $this->creditCardNumber = $creditCardNumber;
    }

    public function __get($property)
    {
        return $this->$property;
    }

    public function pay()
    {
        $this->status = self::PAID;
        $this->commande->setStatus(Commande::STATUS_PAYE);
    }
}
