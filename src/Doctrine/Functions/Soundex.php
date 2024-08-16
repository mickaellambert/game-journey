<?php

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\TokenType;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

class Soundex extends FunctionNode
{
    /**
     * @var Node
     */
    public $stringExpression = null;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'SOUNDEX(' . $this->stringExpression->dispatch($sqlWalker) . ')';
    }
    
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->stringExpression = $parser->ArithmeticPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
