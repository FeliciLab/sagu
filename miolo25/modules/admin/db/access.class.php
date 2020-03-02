<?

class BusinessAdminAccess extends MBusiness implements IAccess
{

    public $idTransaction;

    public $idGroup;

    public $rights;

    public $group; // a Group object

    
    public $transaction; // a Transaction object

    
    public function _construct($data = NULL)
    {
        return parent::_construct( 'admin', $data );
    }

    public function getArrayGroups()
    {
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase('admin');
        
        $sql = "select idgroup, m_group from miolo_group";
        
        $result = $db->query( $sql )->result;
        return $result;
    }

    public function updateGroupAccess($idGroup, $access)
    {
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase( 'admin' );
        
        $sql = 'BEGIN';
        $db->execute( $sql );
        
        $sql = new MSQL( '', 'miolo_access', "idGroup = {$idGroup}" );
        $db->execute( $sql->delete() );
        
        foreach ( (array) $access as $ac )
        {
            $sql = new MSQL( 'idTransaction, idGroup, rights', 'miolo_access' );
            $db->execute( $sql->insert( array( 
                    $ac->transaction, 
                    $idGroup, 
                    $ac->rights 
            ) ) );
        }
        
        $sql = 'COMMIT';
        $db->execute( $sql );
    }
    
    public function deleteAccessByGroup($idGroup)
    {
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase( 'admin' );
        
        $sql = new MSQL( '', 'miolo_access', "idGroup = {$idGroup}" );
        $db->execute( $sql->delete() );
    }
}
?>
