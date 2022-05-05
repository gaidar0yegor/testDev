<?php


namespace App\SocieteProduct\Product;


class StandardProduct extends StarterProduct
{
    public const PRODUCT_KEY = 'STANDARD';

    public const STANDARD_PRIVILEGES = [
        ProductPrivileges::FAIT_MARQUANT_DESCRIPTION_SIZE,
        ProductPrivileges::FAIT_MARQUANT_DATE,
        ProductPrivileges::FAIT_MARQUANT_SEND_MAIL,
        ProductPrivileges::SMS_NOTIFICATION_SAISIE_TEMPS,
        ProductPrivileges::PLANIFICATION_PROJET_AVANCE,
        ProductPrivileges::FICHIER_PROJET_ACCESSES
    ];

    public function __construct()
    {
        parent::__construct();
        $this->setAllPrivileges();
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
                $this::STANDARD_PRIVILEGES
            )
        );
    }
}