<?php
class BusinessAdminModule extends MBusiness //implements IModule
{
    private $MIOLO, $db, $MSQL;
    public $idModule;
    public $name;
    public $description;

    public function __construct($data = NULL)
    {
        parent::__construct('admin',$data);

        $this->MIOLO = MIOLO::getInstance();
        $this->db = $this->MIOLO->getDatabase('admin');
        $this->setdata(null);
        $columns = '*';
        $tables  = 'miolo_module';

        $this->MSQL = new MSQL( $columns, $tables, '', '' );
    }

    public function setData($data)
    {
        $this->idModule    = $data->idModule;
        $this->name        = $data->name;
        $this->description = $data->description;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->idModule;
    }

    public function save()
    {
        parent::save();
    }

    public function delete()
    {
        parent::delete();
    }

    public function getById($id)
    {
        $this->idModule = $id;
        $this->retrieve();
        return $this;
    }

    public function listRange($range = NULL)
    {
        $criteria =  $this->getCriteria();
        $criteria->setRange($range);
        return $criteria->retrieveAsQuery();
    }

    public function listById($id)
    {
        $criteria =  $this->getCriteria();
        $criteria->addCriteria('idModule','LIKE',"'{$id}%'");
        //$criteria->addOrderAttribute('name');
        return $criteria->retrieveAsQuery();
    }


    public function listAll()
    {
        $criteria =  $this->getCriteria();
        return $criteria->retrieveAsQuery();
    }

    public function insert()
    {
        $this->MSQL->clear();
        $columns = 'idModule, description, name';
        $this->MSQL->setColumns($columns);
        $this->MSQL->setTables('miolo_module');

        $sql = $this->MSQL->insert(array($this->idModule, $this->name, $this->description));
        //echo $sql;

        return $this->db->execute($sql);
    }

}
?>
