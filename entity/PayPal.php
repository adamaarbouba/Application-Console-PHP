<?php

use Dom\Comment;

include_once "./entity/Payment.php";


class PayPal extends Payment
{

    private $paymentEmail;
    private $paymentPassword;

    public function __construct($montant, $paymentEmail,$paymentPassword)
    {
        parent::__construct($montant);
        $this->paymentEmail = $paymentEmail;
        $this->paymentPassword = $paymentPassword;
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
