<?php
class MList
{
    public  $items;
    private $count;

    public function __construct()
    {
        $this->items = array
            (
            );

        $this->count = 0;
    }

    public function add($item, $key = NULL)
    {
        if (is_null($key))
        {
            $this->items[$this->count++] = $item;
        }
        else
        {
            $this->items[$key] = $item;
            $this->count++;
        }
    }

    public function clear()
    {
        $this->items = array
            (
            );

        $this->count = 0;
    }

    public function delete($key)
    {
        if (array_key_exists($key, $this->items))
        {
            unset($this->items[$key]);
            $this->count--;
        }
    }

    public function insert($item, $key = 0)
    {
        if (is_numeric($key))
        {
            if ($key < $this->count)
            {
                for ($i = $this->count; $i >= $key; $i--)
                    $this->items[$i] = $this->items[$i - 1];
            }
            else
            {
                $key = $this->count;
            }
        }

        $this->add($item, $key);
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->items))
        {
            return $this->items[$key];
        }
    }

    public function getItems( )
    {
        return $this->items;
    }

    public function setItems( $items )
    {
        $this->items = $items;
    }


    public function set($key, $item)
    {
        if (array_key_exists($key, $this->items))
        {
            $this->items[$key] = $item;
        }
    }

    public function hasItems()
    {
        return ($this->count > 0);
    }
}

class MStringList extends MList
{
    public $duplicates;

    public function __construct($duplicates = true)
    {
        parent::__construct();
        $this->duplicates = $duplicates;
    }

    public function add($item, $key = NULL)
    {
        if (MUtil::array_search_recursive($item, $this->items))
            if (!$this->duplicates)
                return;

        parent::add($item, $key);
    }

    public function addValue($name, $value)
    {
        $this->add($value, $name);
    }

    public function find($value)
    {
        return array_search( $value, $this->items);
    }

    public function getText($separator = '=', $delimiter = ',')
    {
        $s = '';

        foreach ($this->items as $name => $value)
        {
            $s .= (($s != '') ? $delimiter : '') . (($value != '') ? "$name{$separator}$value" : $name);
        }

        return $s;
    }

    public function getValueText($separator = '=', $delimiter = ',')
    {
        $s = '';

        foreach ($this->items as $value)
        {
            $s .= (($s != '') ? $delimiter : '') . $value;
        }

        return $s;
    }

    public function getTextByTemplate($template)
    {
        $s = '';

        foreach ($this->items as $name => $value)
        {
            $s .= str_replace('/:n/', $name, str_replace('/:v/', $value, $template));
        }

        return $s;
    }
}

class MObjectList extends MList
{
}
?>
