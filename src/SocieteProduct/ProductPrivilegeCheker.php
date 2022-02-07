<?php

namespace App\SocieteProduct;

use App\Entity\Societe;
use App\SocieteProduct\Product\PremiumProduct;
use App\SocieteProduct\Product\StandardProduct;
use App\SocieteProduct\Product\StarterProduct;

class ProductPrivilegeCheker
{
    public static function checkProductPrivilege(Societe $societe, string $productPrivilege): bool
    {
        switch ($societe->getProductKey()){
            case PremiumProduct::PRODUCT_KEY:
                $privileges = (new PremiumProduct())->allPrivileges;
                break;
            case StandardProduct::PRODUCT_KEY:
                $privileges = (new StandardProduct())->allPrivileges;
                break;
            default :
                $privileges = (new StarterProduct())->allPrivileges;
        }

        return in_array($productPrivilege, $privileges);
    }
}