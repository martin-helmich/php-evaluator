<?php
namespace Helmich\PhpEvaluator\Evaluator;


use Helmich\PhpEvaluator\Exception\UnknownSymbolException;

class FunctionStore implements \ArrayAccess
{



    private $functions = [];


    private $fallback = NULL;



    public function setFallbackFunction(callable $fallback)
    {
        $this->fallback = $fallback;
    }



    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->functions);
    }



    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->functions))
        {
            return $this->functions[$offset];
        }
        else if (NULL !== $this->fallback)
        {
            $fallback = $this->fallback;
            return function () use ($offset, $fallback)
            {
                $args = func_get_args();
                return call_user_func($fallback, $offset, $args);
            };
        }
        throw new UnknownSymbolException('Function ' . $offset . ' was not found!');
    }



    public function offsetSet($offset, $value)
    {
        $this->functions[$offset] = $value;
    }



    public function offsetUnset($offset)
    {
        unset($this->functions[$offset]);
    }
}