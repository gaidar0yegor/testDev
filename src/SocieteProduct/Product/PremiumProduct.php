<?php


namespace App\SocieteProduct\Product;


class PremiumProduct extends StandardProduct
{
    public const PRODUCT_KEY = 'PREMIUM';

    public const PREMIUM_PRIVILEGES = [
        ProductPrivileges::MULTI_SOCIETE_DASHBOARD,
        ProductPrivileges::SOCIETE_HIERARCHICAL_SUPERIOR
    ];

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
        $this->allPrivileges = array_unique(
            array_merge(
                $this::STARTER_PRIVILEGES,
                $this::STANDARD_PRIVILEGES,
                $this::PREMIUM_PRIVILEGES
            )
        );
    }
}