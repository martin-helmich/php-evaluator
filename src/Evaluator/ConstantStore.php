<?php
namespace Helmich\PhpEvaluator\Evaluator;


use Helmich\PhpEvaluator\Exception\UnknownSymbolException;

class ConstantStore extends VariableStore
{



    public function __construct(array $vars = [])
    {
        $vars['TRUE']  = \TRUE;
        $vars['FALSE'] = \FALSE;
        $vars['NULL']  = \NULL;

        parent::__construct($vars);
    }



    /**
     * @param $offset
     * @throws UnknownSymbolException
     */
    protected function throwUnknownException($offset)
    {
        throw new UnknownSymbolException('Constant ' . $offset . ' is not defined!');
    }

}