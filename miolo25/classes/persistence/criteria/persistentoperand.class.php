<?
class PersistentOperand
{
    public $criteria;
    public $operand;
    public $type;

    public function __construct($criteria, $operand)
    {
        $this->criteria = $criteria;
        $this->operand = $operand;
    }

    public function getSql()
    {
        return '';
    }

    public function getSqlWhere()
    {
        return $this->getSql();
    }
}

class OperandNull extends PersistentOperand
{
    public function __construct($criteria, $operand)
    {
        parent::__construct($criteria, $operand);
        $this->type = 'null';
    }
}

class OperandValue extends PersistentOperand
{
    public function __construct($criteria, $operand)
    {
        parent::__construct($criteria, $operand);
        $this->type = 'value';
    }

    public function getSql()
    {
        $value = $this->operand; 
        if ($value{0} != '?')
        {
            if ($value{0} == ':')
            {
                $value = substr($value,1);
            }
            elseif (($value === '') || (strtolower($value)=='null') || is_null($value))
            {
                $value = 'null';
            }
            elseif ($value{0} != "'")
            {
                $value = "'".addslashes($value)."'";
            }
        }
        return $value;
    }
}

class OperandAttributeMap extends PersistentOperand
{
    public $attributeMap;
    public $alias = '';

    public function __construct($criteria, $operand, $name)
    {
        parent::__construct($criteria, $operand);
        $this->type = 'attributemap';
        if ($p = strpos($name,'.')) $this->alias = substr($name,0, $p);
        $this->attributeMap = $operand;
    }

    public function getSql()
    {
        return $this->attributeMap->getColumnMap()->getColumnName($this->alias, FALSE,$this->attributeMap->getClassMap());
    }

    public function getSqlName()
    {
        return $this->attributeMap->getColumnMap()->getName();
    }

    public function getSqlOrder()
    {
        return $this->attributeMap->getColumnMap()->getFullyQualifiedName($this->alias);
    }

    public function getSqlWhere()
    {
        return $this->attributeMap->getColumnMap()->getColumnWhereName($this->alias,$this->attributeMap->getClassMap());
    }
}

class OperandArray extends PersistentOperand
{
    public function __construct($criteria, $operand)
    {
        parent::__construct($criteria, $operand);
        $this->type = 'array';
    }

    public function getSql()
    {
        $sql = "(";
        $i = 0; 
        foreach($this->operand as $o)
        {
            $sql .= ($i++ > 0) ? ", " : "";
            $sql .= "'$o'";
        }
        $sql .= ")";
        return $sql;
    }
}

class OperandCriteria extends PersistentOperand
{
    public function __construct($criteria, $operand)
    {
        parent::__construct($criteria, $operand);
        $this->type = 'criteria';
    }

    public function getSql()
    {
        $sql = $this->operand->getSqlStatement();
        $sql->setDb($this->operand->getManager()->getConnection($this->operand->getClassMap()->getDatabase()));
        return "(" . $sql->select() . ")";
    }
}

class OperandObject extends PersistentOperand
{
    public function __construct($criteria, $operand)
    {
        parent::__construct($criteria, $operand);
        $this->type = 'object';
    }

    public function getSql()
    {
        return $this->operand->getSql();
    }
}

class OperandFunction extends PersistentOperand
{
    public $argument;
    public $argOperand;

    public function __construct($criteria, $operand)
    {
        parent::__construct($criteria, $operand);
        $this->type = 'public function';
        $this->argument = $this->operand;
        preg_match("/(?i)\(([^,\+\*\/\-]*)([,\+\*\/\-]*)(.*)\)/", $this->operand, $matches);
        if (count($matches) > 1)
        {
            for ($i=1; $i < count($matches); $i++)
            {
                $mat = trim($matches[$i]);
                if ($mat != '')
                {
                    $op = $criteria->getOperand($mat);
                    if (get_class($op) == 'OperandValue')
                    {
                        $op = $criteria->getOperand(':'.$mat);
                    }
                    $this->argument = str_replace($mat,$op->getSql(), $this->argument);
                }
            }
        }
        else
           echo "error OperandFunction"; 
    }

    public function getSql()
    {
        return $this->argument;
    }
}