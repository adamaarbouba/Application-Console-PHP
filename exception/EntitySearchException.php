<?php

class EntitySearchException extends Exception {

    public function __construct(string $message = "", int $code = 0)
    {
        return parent::__construct($message, $code);
    }
}

