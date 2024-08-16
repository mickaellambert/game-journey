<?php 

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\TokenType;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

class Levenshtein extends FunctionNode
{
    /**
     * @var Node
    */
    public $firstStringExpression = null;

    /**
     * @var Node
    */
    public $secondStringExpression = null;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'LEVENSHTEIN(' .
            $this->firstStringExpression->dispatch($sqlWalker) . ', ' .
            $this->secondStringExpression->dispatch($sqlWalker) .
            ')';
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->firstStringExpression = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->secondStringExpression = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
