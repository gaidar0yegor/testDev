<?php

namespace App\SocieteProduct\Product;


abstract class AbstractSocieteProduct
{
    public string $productKey;

    /**
     * Toutes fonctionnalités du produit
     *
     * @var string[]
     */
    public array $allPrivileges = [];

    public function __construct()
    {
        $this->setProductKey();
        $this->setAllPrivileges();
    }

    abstract protected function setProductKey();

    abstract protected function setAllPrivileges();
}