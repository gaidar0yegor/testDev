<?php

namespace App\SocieteProduct\Twig;

use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\SocieteProduct\ProductPrivilegeCheker;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductPrivilegeChekerExtension extends AbstractExtension
{
    private UserContext $userContext;
    private ProductPrivilegeCheker $productPrivilegeCheker;

    public function __construct(UserContext $userContext, ProductPrivilegeCheker $productPrivilegeCheker)
    {
        $this->userContext = $userContext;
        $this->productPrivilegeCheker = $productPrivilegeCheker;
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('productPrivilegeCheker', [$this, 'productPrivilegeCheker']),
            new TwigFunction('userProductPrivilegeCheker', [$this, 'userProductPrivilegeCheker']),
        ];
    }

    public function productPrivilegeCheker(string $privilege) :bool
    {
        return $this->productPrivilegeCheker->checkProductPrivilege($this->userContext->getSocieteUser()->getSociete() ,$privilege);
    }

    public function userProductPrivilegeCheker(string $privilege) :bool
    {
        $societes = $this->userContext->getUser()->getSocieteUsers()->map(function (SocieteUser $societeUser){
            return $societeUser->getSociete();
        });

        foreach ($societes as $societe){
            if ($this->productPrivilegeCheker->checkProductPrivilege($societe ,$privilege)){
                return true;
            }
        }

        return false;
    }
}
