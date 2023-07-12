<?php
declare(strict_types=1);

namespace App\Doctrine\ORM\Query\AST\Platform\Postgresql;

use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\SqlWalker;
use App\Doctrine\ORM\Query\AST\Platform\PlatformFunctionNode;
use App\Doctrine\ORM\Query\AST\Functions\SimpleFunction;

class Year extends PlatformFunctionNode
{
	protected function getTimestampValue($expression, SqlWalker $sqlWalker): string
    {
        $value = $this->getExpressionValue($expression, $sqlWalker);
        if ($expression instanceof Literal) {
            $value = \trim(\trim($value), '\'"');
            /** @noinspection SubStrUsedAsArrayAccessInspection */
            if (\is_numeric(\substr($value, 0, 1))) {
                $timestampFunction = new Timestamp([SimpleFunction::PARAMETER_KEY => "'$value'"]);
                $value = $timestampFunction->getSql($sqlWalker);
            }
        }

        return $value;
    }
	
    public function getSql(SqlWalker $sqlWalker): string
    {
        /** @var Node $expression */
        $expression = $this->parameters[SimpleFunction::PARAMETER_KEY];
        return 'EXTRACT(YEAR FROM ' . $this->getTimestampValue($expression, $sqlWalker) . ')';
    }
}
