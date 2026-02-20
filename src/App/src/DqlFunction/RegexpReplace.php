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
class RegexpReplace extends FunctionNode
{
    public $stringFirst;
    public $stringSecond;
    public $stringThird;

    /**
     * @override
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->stringFirst = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);

        $this->stringSecond = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);

        $this->stringThird = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @override
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'REGEXP_REPLACE(' . $sqlWalker->walkArithmeticPrimary($this->stringFirst) . ',' . $sqlWalker->walkArithmeticPrimary($this->stringSecond) . ',' . $sqlWalker->walkArithmeticPrimary($this->stringThird) . ')';
    }
}