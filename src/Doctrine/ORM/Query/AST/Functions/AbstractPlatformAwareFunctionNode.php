<?php

declare(strict_types=1);



namespace App\Doctrine\ORM\Query\AST\Functions;



use Doctrine\ORM\Query\AST\Functions\FunctionNode;

use Doctrine\ORM\Query\SqlWalker;

use App\Doctrine\ORM\Query\AST\FunctionFactory;



abstract class AbstractPlatformAwareFunctionNode extends FunctionNode

{

    /** @var array */

    public $parameters = [];



    /**

     * @noinspection PhpMissingReturnTypeInspection

     * @noinspection ReturnTypeCanBeDeclaredInspection

     */

    public function getSql(SqlWalker $sqlWalker)

    {

        $function = FunctionFactory::create(

            $sqlWalker->getConnection()->getDatabasePlatform()->getName(),

            $this->name,

            $this->parameters

        );

        return $function->getSql($sqlWalker);

    }

}
