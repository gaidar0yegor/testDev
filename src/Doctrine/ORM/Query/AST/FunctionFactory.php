<?php

declare(strict_types=1);



namespace App\Doctrine\ORM\Query\AST;



use Doctrine\ORM\Query\QueryException;

use App\Doctrine\ORM\Query\AST\Platform\PlatformFunctionNode;



class FunctionFactory

{

    /**

     * Create platform function node.

     *

     * @throws QueryException

     */

    public static function create(string $platformName, string $functionName, array $parameters): PlatformFunctionNode

    {

        $className = __NAMESPACE__

            . '\\Platform\\'

            . static::classify(\strtolower($platformName))

            . '\\'

            . static::classify(\strtolower($functionName));


        //echo $className;
        if (!\class_exists($className)) {

            throw QueryException::syntaxError(

                \sprintf(

                    'Function "%s" does not supported for platform "%s"',

                    $functionName,

                    $platformName

                )

            );

        }



        return new $className($parameters);

    }



    private static function classify($word)

    {

        return \str_replace([' ', '_', '-'], '', \ucwords($word, ' _-'));

    }

}
