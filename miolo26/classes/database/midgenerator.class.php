<?
abstract class MIdGenerator
{
    private $value = 0;
    public $db;

    public function __construct($db)
    {
       $this->db = $db;
    }

    abstract public function getNewId($sequence='admin', $tableGenerator = 'cm_sequence');
    abstract public function getNextValue($sequence='admin', $tableGenerator = 'cm_sequence');
}



