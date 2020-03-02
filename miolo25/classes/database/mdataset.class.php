<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MDataSet
{
    /**
     * Attribute Description.
     */
    public $result; // the dataset (an numeric-indexed array of rows x fields)

    /**
     * Attribute Description.
     */
    public $row; // the current row index

    /**
     * Attribute Description.
     */
    public $rowCount; // the row count

    /**
     * Attribute Description.
     */
    public $colCount; // the col count

    /**
     * Attribute Description.
     */
    public $eof; // true if row > nrows

    /**
     * Attribute Description.
     */
    public $bof; // true if row < 0

    /**
     * Attribute Description.
     */
    public $metadata; // an array with fieldname, fieldtype, fieldlength, fieldpos for the result

    /**
     * Attribute Description.
     */
    public $type; // query, table or storedproc

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function __construct()
    {
        $this->eof = $this->bof = true;
        $this->result = array
            (
            );
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function movePrev()
    {
        if ($this->bof = (--$this->row < 0))
        {
            $this->row = 0;
        }

        return $this->bof;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function moveNext()
    {
        if ($this->eof = (++$this->row >= $this->rowCount))
        {
            $this->row = $this->rowCount - 1;
        }

        return $this->eof;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $row (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function moveTo($row)
    {
        $inRange = (!$this->eof) && (($row < $this->rowCount) && ($row > -1));

        if ($inRange)
        {
            $this->row = $row;
            $this->bof = $this->eof = false;
        }

        return $inRange;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function moveFirst()
    {
        return $this->moveTo(0);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function moveLast()
    {
        return $this->moveTo($this->rowCount - 1);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getColumnCount()
    {
        return $this->colCount;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $colNumber (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function getColumnName($colNumber)
    {
        return $this->metadata['fieldname'][$colNumber];
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getColumnNames()
    {
        return $this->metadata['fieldname'];
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $colName (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function getColumnNumber($colName)
    {
        return $this->metadata['fieldpos'][strtoupper($colName)];
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $colName (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function getValue($colName)
    {
        $result = $this->result[$this->row][$this->metadata['fieldpos'][strtoupper($colName)]];
        return $result;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $fieldName (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function fields($fieldName)
    {
        $result = $this->result[$this->row][$this->metadata['fieldpos'][strtoupper($fieldName)]];
        return $result;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getRowValues()
    {
        return $this->result[$this->row];
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getRowObject()
    {
        $data = new RowObject();
        return $this->setRowObject($data);
    }

    public function &SetRowObject($object)
    {
        for ($i = 0; $i < $this->colCount; $i++)
        {
            $fieldName = strtolower($this->metadata['fieldname'][$i]);
            $object->$fieldName = $this->result[$this->row][$i];
        }

        return $object;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getFieldValues()
    {
        $fieldvalues = array
            (
            );

        for ($i = 0; $i < $this->colCount; $i++)
            $fieldvalues[$this->metadata['fieldname'][$i]] = $this->result[$this->row][$i];

        return $fieldvalues;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function eof()
    {
        return (($this->eof) or ($this->rowCount == 0));
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function bof()
    {
        return (($this->bof) or ($this->rowCount == 0));
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $key0 (tipo) desc
     * @param $value=1 (tipo) desc
     * @param $showKeyValue= (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function chunkResult($key = 0, $value = 1, $showKeyValue = false)
    {
        $newResult = array
            (
            );
        if ($rs = $this->result)
        {
            foreach ($rs as $row)
            {
                $sKey = trim($row[$key]);
                $sValue = trim($row[$value]);
                $newResult[$sKey] = ($showKeyValue ? $sKey . " - " : '') . $sValue;
            }
        }
        return $newResult;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $key (tipo) desc
     * @param $values (tipo) desc
     * @param $typeS' (tipo) desc
     * @param $separator='' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function chunkResultMany($key, $values, $type = 'S', $separator = '')
    {
        // type= 'S' : string, otherwise array
        $newResult = array
            (
            );

        if ($rs = $this->result)
        {
            if (!is_array($values))
                $values = array($values);

            foreach ($rs as $row)
            {
                $sKey = trim($row[$key]);

                if ($type == 'S')
                {
                    $sValue = '';

                    foreach ($values as $v)
                        $sValue .= trim($row[$v]) . $separator;
                }
                else
                {
                    $sValue = array
                        (
                        );

                    foreach ($values as $v)
                        $sValue[] = trim($row[$v]);
                }

                $newResult[$sKey] = $sValue;
            }

            return $newResult;
        }
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $group (tipo) desc
     * @param $node (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function treeResult($group, $node)
    {
        $tree = array
            (
            );

        if ($rs = $this->result)
        {
            $tree = array
                (
                );

            $node = explode(',', $node);
            $group = explode(',', $group);

            foreach ($rs as $row)
            {
                $aNode = array
                    (
                    );

                foreach ($node as $n)
                    $aNode[] = $row[$n];

                $s = '';

                foreach ($group as $g)
                    $s .= '[$row[' . $g . ']]';

                eval ("\$tree$s" . "[] = \$aNode;");
            }
        }

        return $tree;
    }

    public function asXML($root = 'root', $node = 'node')
    {
        $xml = "<$root>";
        $this->moveFirst();
        while (!$this->eof)
        {
            $xml .= "<$node>";
            for ($i = 0; $i < $this->colCount; $i++)
            {
                $fieldName = strtolower($this->metadata['fieldname'][$i]);
                $xml .= "<$fieldName>" . $this->result[$this->row][$i] .  "</$fieldName>";
            }
            $this->moveNext();
            $xml .= "</$node>";
        }
        $xml .= "</$root>";
        return $xml;
    }

}

/**
 * Brief Class Description.
 * Complete Class Description.
 */
class RowObject
{
}
?>
