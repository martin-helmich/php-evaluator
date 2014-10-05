<?php
namespace Helmich\PhpEvaluator\Evaluator;


use Helmich\PhpEvaluator\Exception\UnknownSymbolException;

class VariableStore implements \ArrayAccess
{



    /**
     * @var callable
     */
    private $lookupFunction = NULL;


    private $vars = [];



    public function __construct(array $vars = [])
    {
        $this->vars = $vars;
    }



    public function setLookupFunction(callable $lookupFunction)
    {
        $this->lookupFunction = $lookupFunction;
    }



    public function offsetExists($offset)
    {
        if (array_key_exists($offset, $this->vars))
        {
            return TRUE;
        }
        return FALSE;
    }



    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->vars))
        {
            return $this->vars[$offset];
        }
        else if (NULL !== $this->lookupFunction)
        {
            return call_user_func($this->lookupFunction, $offset);
        }
        $this->throwUnknownException($offset);
    }



    public function offsetSet($offset, $value)
    {
        $this->vars[$offset] = $value;
    }



    public function offsetUnset($offset)
    {
        unset($this->vars[$offset]);
    }



    /**
     * @param $offset
     * @throws UnknownSymbolException
     */
    protected function throwUnknownException($offset)
    {
        throw new UnknownSymbolException('Variable $' . $offset . ' is not defined!');
    }
}