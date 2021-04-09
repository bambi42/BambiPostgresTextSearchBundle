<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\Doctrine\Query;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class ToTsquery
 * "to_tsquery" "(" StringPrimary "," StringPrimary ")"
 *
 * @package App\Bambi\Query
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
class ToTsquery extends FunctionNode
{
    public $languageExpression;
    public $queryExpression;

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'to_tsquery(' .
            $this->languageExpression->dispatch($sqlWalker) . ', ' .
            $this->queryExpression->dispatch($sqlWalker) . ')';
    }

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->languageExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->queryExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}