<?php

class MStackHistory
{
    public $limit = 10;
    public $name;
    public $base = 0;
    public $top;
    public $stack;

    public function __construct($name)
    {
        $MIOLO = MIOLO::getInstance();

        $this->name = $name;
        $s = $MIOLO->getSession()->getValue($name);
        if ($s == NULL)
        {
            $this->top = -1;
            $this->stack = array();
        }
        else
        {
            $this->top = count($s) - 1;
            $this->stack = $s;
        }
    }

    public function push($value)
    {
        if (++$this->top > $this->limit)
        {
            ++$this->base;
        }
        $this->stack[$this->top] = $value;
    }

    public function pop()
    {
        $value = $this->stack[$this->top];
        if (--$this->top < $this->base)
        { 
            $this->top = $this->base;
        } 
        return $value;
    }

    public function top($offset = 0)
    {
        $n = $this->top - $offset;
        return ($n >= $this->base) ? $this->stack[$n] : NULL;
    }

    public function count()
    {
        return $this->top - $this->base + 1;
    }

    public function save()
    {
        $MIOLO = MIOLO::getInstance();

        $this->stack = array_slice($this->stack, $this->base, $this->count());
        $this->top = $this->count() - 1;
        $MIOLO->getSession()->setValue($this->name, $this->stack);
    }
}

class MHistory extends MService
{
    public $stackContext;
    public $stackHistory;
    public $context;

    public function __construct()
    {
        parent::__construct();
        $this->stackContext = new MStackHistory('_stackContext');
        $this->stackHistory = new MStackHistory('_stackAction');
        if ($this->stackContext->count() == 0)
        {
            $this->context->action = '';
        }
        else
        { 
            $this->context = new MContext($this->stackContext->top());
        }
        $this->push($this->manager->getCurrentUrl());
    }

    public function push($action)
    {
        $this->stackHistory->push($action);
        $context = new MContext($action);
        $this->stackContext->push($context->composeURL($this->manager->dispatch));
    }

    public function pop($stack = NULL)
    {
        if (is_null($stack) || ($stack == 'action'))
        {
            $this->stackHistory->pop();
        }
        if (is_null($stack) || ($stack == 'context'))
        {
            $this->stackContext->pop();
        }
    }

    public function top($stack = 'action')
    {
        if ($stack == 'action')
        {
            return $this->stackHistory->top();
        }
        elseif ($stack == 'context')
        {
            return $this->stackContext->top();
        }
    }

    public function back($stack = 'action')
    {
        if ($stack == 'action')
        {
            return $this->stackHistory->top(1);
        }
        elseif ($stack == 'context')
        {
            return $this->stackContext->top(1);
        }
    }

    public function close()
    {
        $this->stackContext->save();
        $this->stackHistory->save();
    }
}
?>
