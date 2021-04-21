<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\Doctrine\Query;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class Text
 * "text" "(" ArithmeticPrimary ")"
 *
 * @package Bambi\PostgresTextSearchBundle\Doctrine\Query
 * @author Louis Fahrenholz <louis.fahrenholz@posteo.de>
 *
 * @license MIT
 */
class Text extends FunctionNode
{
    public $param;

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'text(' . $this->param->dispatch($sqlWalker) . ')';
    }

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->param = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}