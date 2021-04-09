<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\Doctrine\Query;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class TsMatches
 * "ts_matches" "(" StringPrimary "," StringPrimary ")" => "(" StringPrimary "@@" StringPrimary ")"
 *
 * @package App\Bambi\Query
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
class TsMatches extends FunctionNode
{
    public $tsExpression1;
    public $tsExpression2;

    public function getSql(SqlWalker $sqlWalker)
    {
        return '(' .
            $this->tsExpression1->dispatch($sqlWalker) . '@@ ' .
            $this->tsExpression2->dispatch($sqlWalker) . ')';
    }

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->tsExpression1 = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->tsExpression2 = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}