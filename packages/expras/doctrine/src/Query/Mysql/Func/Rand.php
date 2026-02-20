<?php
/**
 * Author: Stanislav Anisimov<stanislav@ww9.ru>
 * Date: 11.03.13
 * Time: 22:27
 */

namespace ExprAs\Doctrine\Query\Mysql\Func;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class Rand extends FunctionNode
{
    private $seedExpression = null;

    public function parse(Parser $parser)
    {
        // Match the function identifier RAND
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        // Access the lookahead token properly
        $lexer = $parser->getLexer();
        if ($lexer->lookahead !== null && $lexer->lookahead->type !== Lexer::T_CLOSE_PARENTHESIS) {
            $this->seedExpression = $parser->SimpleArithmeticExpression();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        // If a seed is provided, include it in the SQL; otherwise, use RAND()
        if ($this->seedExpression !== null) {
            return sprintf(
                'RAND(%s)',
                $sqlWalker->walkSimpleArithmeticExpression($this->seedExpression)
            );
        }

        return 'RAND()';
    }
}
