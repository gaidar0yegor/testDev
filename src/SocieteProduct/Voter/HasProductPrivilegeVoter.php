<?php

namespace App\SocieteProduct\Voter;

use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\SocieteProduct\ProductPrivilegeCheker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class HasProductPrivilegeVoter extends Voter
{
    private UserContext $userContext;

    public function __construct(
        UserContext $userContext
    ) {
        $this->userContext = $userContext;
    }

    public const NAME = 'has_product_privilege';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports(string $attribute, $subject)
    {
        return self::NAME === $attribute;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param mixed $subject
     *
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        if ($this->userContext->hasSocieteUser()){
            return ProductPrivilegeCheker::checkProductPrivilege($this->userContext->getSocieteUser()->getSociete(), $subject);
        } else {
            $societes = $this->userContext->getUser()->getSocieteUsers()->map(function (SocieteUser $societeUser){
                return $societeUser->getSociete();
            });

            foreach ($societes as $societe){
                if (ProductPrivilegeCheker::checkProductPrivilege($societe, $subject)){
                    return true;
                }
            }
        }

    }
}