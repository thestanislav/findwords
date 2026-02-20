<?php
/**
 * Author: Stanislav Anisimov<stanislav@ww9.ru>
 * Date: 11.03.13
 * Time: 22:27
 */

namespace ExprAs\Doctrine\Query\Mysql\Func;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser as QueryParser;
use Doctrine\ORM\Query\SqlWalker;

class Round extends FunctionNode
{
    /**
     * simpleArithmeticExpression
     *
     * @var    mixed
     * @access public
     */
    public $simpleArithmeticExpression;

    /**
     * getSql
     *
     * @param  \Doctrine\ORM\Query\SqlWalker $sqlWalker
     * @access public
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'ROUND(' .
            $sqlWalker->walkSimpleArithmeticExpression(
                $this->simpleArithmeticExpression
            ) .
            ')';
    }

    /**
     * parse
     *
     * @param  \Doctrine\ORM\Query\Parser $parser
     * @access public
     * @return void
     */
    public function parse(QueryParser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->simpleArithmeticExpression = $parser->SimpleArithmeticExpression();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
