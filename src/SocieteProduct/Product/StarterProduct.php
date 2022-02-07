<?php


namespace App\SocieteProduct\Product;


class StarterProduct extends AbstractSocieteProduct
{
    public const PRODUCT_KEY = 'STARTER';

    public const STARTER_PRIVILEGES = [];

    public function __construct()
    {
        parent::__construct();
    }

    protected function setProductKey()
    {
        $this->productKey = $this::PRODUCT_KEY;
    }

    protected function setAllPrivileges()
    {
        $this->allPrivileges = $this::STARTER_PRIVILEGES;
    }
}