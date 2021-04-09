<?php
declare(strict_types=1);

namespace Bambi\PostgresTextSearchBundle\Doctrine\Query;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class ToTsvector
 * "to_tsvector" "(" StringPrimary "," StringPrimary ")"
 *
 * @package App\Bambi\Query
 * @author Louis Fahrenholz <b4mb1@posteo.de>
 *
 * @license MIT
 */
class ToTsvector extends FunctionNode
{
    public $languageExpression;
    public $columnsExpression;

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'to_tsvector(' .
            $this->languageExpression->dispatch($sqlWalker) . ', ' .
            $this->columnsExpression->dispatch($sqlWalker) . ')';
    }

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->languageExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->columnsExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}