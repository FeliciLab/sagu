<?
class BusinessAdminAccess extends MBusiness implements IAccess
{
    public $idTransaction;
    public $idGroup;
    public $rights;

    public $group;  // a Group object
    public $transaction; // a Transaction object

    public function _construct($data = NULL)
    {
        return parent::_construct('admin', $data);
    }
}
?>
