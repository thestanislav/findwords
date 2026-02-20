<?php

/**
 * DoctrineExtensions Mysql Function Pack
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace App\DqlFunction;

use Doctrine\ORM\Query\AST\Functions\FunctionNode,
    Doctrine\ORM\Query\Lexer;

/**
 * "CHAR_LENGTH" "(" SimpleArithmeticExpression ")"
 *
 * @category DoctrineExtensions
 * @package  AsDoctrine\Query\Mysql\Func
 * @author   Metod <metod@simpel.si>
 * @license  BSD License
 */
class Levenshtein extends FunctionNode
{
    private $expr1;

    private $expr2;

    /**
     * @override
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->expr1 = $parser->ArithmeticExpression();

        $parser->match(Lexer::T_COMMA);

        $this->expr2 = $parser->ArithmeticExpression();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @override
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'levenshtein(' . $sqlWalker->walkArithmeticPrimary($this->expr1) . ', ' . $sqlWalker->walkArithmeticPrimary($this->expr2) . ')';
    }
}