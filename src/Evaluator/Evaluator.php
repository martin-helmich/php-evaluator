<?php
namespace Helmich\PhpEvaluator\Evaluator;


use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;

class Evaluator
{



    /**
     * @var VariableStore
     */
    private $constants;


    /**
     * @var VariableStore
     */
    private $globals;


    /**
     * @var FunctionStore
     */
    private $functions;



    public function setConstantStore(VariableStore $constants)
    {
        $this->constants = $constants;
    }



    public function setGlobalScope(VariableStore $globals)
    {
        $this->globals = $globals;
    }



    public function setFunctionStore(FunctionStore $functions)
    {
        $this->functions = $functions;
    }



    /**
     * @param Expr $expression
     * @return mixed
     * @throws \Exception
     */
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
        elseif ($expression instanceof Expr\ConstFetch)
        {
            return $this->constants[$expression->name->toString()];
        }
        elseif ($expression instanceof Expr\New_)
        {
            $class = $this->evaluateNameOrExpression($expression);

            $argValues = array_map(
                function (Arg $arg) { return $this->evaluateExpression($arg->value); },
                $expression->args
            );

            $refl = new \ReflectionClass($class);
            return $refl->newInstanceArgs($argValues);
        }
        elseif ($expression instanceof Expr\Instanceof_)
        {
            $class = $this->evaluateNameOrExpression($expression->class);
            $expr  = $this->evaluateExpression($expression->expr);
            return $expr instanceof $class;
        }
        elseif ($expression instanceof Expr\Array_)
        {
            return $this->evaluateArray($expression);
        }
        elseif ($expression instanceof Expr\BinaryOp\Concat)
        {
            return $this->evaluateExpression($expression->left) . $this->evaluateExpression($expression->right);
        }
        elseif ($expression instanceof Expr\StaticCall)
        {
            $class     = $this->evaluateNameOrExpression($expression->class);
            $name      = ($expression->name instanceof Expr) ? $this->evaluateExpression(
                $expression->name
            ) : $expression->name;
            $argValues = array_map(
                function (Arg $arg) { return $this->evaluateExpression($arg->value); },
                $expression->args
            );

            $function = $this->functions[$class . '::' . $name];
            return call_user_func_array($function, $argValues);
        }
        elseif ($expression instanceof Expr\Variable)
        {
            $name = ($expression->name instanceof Expr)
                ? $this->evaluateExpression($expression->name)
                : $expression->name;
            return $this->globals[$name];
        }
        elseif ($expression instanceof Expr\ArrayDimFetch)
        {
            $var = $this->evaluateExpression($expression->var);
            $dim = $expression->dim ? $this->evaluateExpression($expression->dim) : NULL;
            return $dim ? $var[$dim] : NULL;
        }
        elseif($expression instanceof Expr\UnaryMinus)
        {
            return - $this->evaluateExpression($expression->expr);
        }
        throw new \Exception('Cannot handle node ' . get_class($expression) . '!');
    }



    private function evaluateArray(Expr\Array_ $array)
    {
        $value = [];

        foreach ($array->items as $item)
        {
            $key       = $item->key ? $this->evaluateExpression($item->key) : NULL;
            $itemValue = $this->evaluateExpression($item->value);

            if ($key)
            {
                $value[$key] = $itemValue;
            }
            else
            {
                $value[] = $itemValue;
            }
        }
        return $value;
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
            return (int)$evaldExpression;
        }
        elseif ($cast instanceof Expr\Cast\Double)
        {
            return (double)$evaldExpression;
        }
        elseif ($cast instanceof Expr\Cast\String)
        {
            return (string)$evaldExpression;
        }
        elseif ($cast instanceof Expr\Cast\Array_)
        {
            return (array)$evaldExpression;
        }
        elseif ($cast instanceof Expr\Cast\Bool)
        {
            return (bool)$evaldExpression;
        }
        elseif ($cast instanceof Expr\Cast\Object)
        {
            return (object)$evaldExpression;
        }
        return $evaldExpression;
    }



    /**
     * @param Node $node
     * @return null|string|void
     */
    private function evaluateNameOrExpression(Node $node)
    {
        if ($node instanceof Expr)
        {
            return $this->evaluateExpression($node);
        }
        elseif ($node instanceof Name)
        {
            return $node->toString();
        }
        return NULL;
    }
} 