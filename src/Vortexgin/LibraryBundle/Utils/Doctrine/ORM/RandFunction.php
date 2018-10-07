<?php

namespace Vortexgin\LibraryBundle\Utils\Doctrine\ORM;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * MySQL RAND Function
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils\Doctrine\ORM
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class RandFunction extends FunctionNode
{

    /**
     * Parse function
     * 
     * @param Doctrine\ORM\Query\Parser $parser Query parser
     * 
     * @return void
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Function to get SQL
     * 
     * @param Doctrine\ORM\Query\SqlWalker $sqlWalker SQL walker
     * 
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'RAND()';
    }
}