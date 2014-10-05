<?php
namespace Helmich\PhpEvaluator\Evaluator;


use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;

class Evaluator
{



    public function evaluateExpression(Expr $expression)
    {
        if ($expression instanceof Scalar)
        {
            return $this->evaluateScalar($expression);
        }
        elseif ($expression instanceof Expr\Cast)
        {
            return $this->evaluateCast($expression);
        }
    }



    private function evaluateScalar(Scalar $scalar)
    {
        if (isset($scalar->value))
        {
            return $scalar->value;
        }
        return NULL;
    }



    private function evaluateCast(Expr\Cast $cast)
    {
        $evaldExpression = $this->evaluateExpression($cast->expr);

        if ($cast instanceof Expr\Cast\Int)
        {
            return (int) $evaldExpression;
        }
        elseif ($cast instanceof Expr\Cast\Double)
        {
            return (double) $evaldExpression;
        }
        elseif ($cast instanceof Expr\Cast\String)
        {
            return (string) $evaldExpression;
        }
        elseif ($cast instanceof Expr\Cast\Array_)
        {
            return (array) $evaldExpression;
        }
        elseif ($cast instanceof Expr\Cast\Bool)
        {
            return (bool) $evaldExpression;
        }
        elseif ($cast instanceof Expr\Cast\Object)
        {
            return (object) $evaldExpression;
        }
        return $evaldExpression;
    }
} 