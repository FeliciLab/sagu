<?

class BusinessBaseLookup
{

    function lookupUser(&$lookup)
    {
        $module = MIOLO::getCurrentModule();
        
        $filterLogin = $lookup->getFilterValue( 'filterLogin' );
        if (!$filterLogin) 
        {
            $filterLogin = $lookup->GetFilterValue();
        } 
        $filterName = $lookup->getFilterValue( 'filterName' );
        $lookup->addFilterField( new TextField( 'filterLogin', $filterLogin, _M('Login', $module), 20 ) );
        $lookup->addFilterField( new TextField( 'filterName', $filterName, _M('Name', $module), 40 ) );
        $columns = array( 
                new DataGridColumn( 'idUser', _M('User id', $module), 'center', true, '15%', true ), 
                new DataGridColumn( 'login', _M('Login', $module), 'left', true, '20%', true ), 
                new DataGridColumn( 'name', _M('Name', $module), 'left', true, '40%', true ), 
                new DataGridColumn( 'nickname', _M('Nickname', $module), 'left', true, '25%', true ) 
        );
        $sql = new MSQL( "iduser, login, name, nickname", 'miolo_user');
        
        if ( $filterLogin || $filterName )
        {
            $sql->where .= "(";
            if ( $filterLogin )
            {
                $sql->where .= "( upper(login) like upper('{$filterLogin}%') )";
            }
            if ( $filterName )
            {
                if ( $filterLogin )
                {
                    $sql->where .= ' AND ';
                }
                $sql->where .= " ( upper(name) like upper('{$filterName}%') )";
            }
            $sql->where .= " )";
        }
        $lookup->setGrid( 'base', $sql, $columns, 'Lookup Users', 10, 0 );
    }

    function lookupTransaction(&$lookup)
    {
        $filter = $lookup->getFilterValue( 'filter' );
        if ( ! $filter )
        {
            $filter = $lookup->getFilterValue();
        }
        $lookup->addFilterField( new MTextField( 'filter', $filter, 'TransaÃ§Ã£o', 20 ) );
        $columns = array( 
                new DataGridColumn( 'idtransaction', 'Id', 'right', true, '5%', true ), 
                new DataGridColumn( 'm_transaction', 'Transaction', 'left', true, '95%', true ) 
        );
        $sql = new sql( 'idtransaction, m_transaction', 'miolo_transaction', '', 'm_transaction' );
        if ( $filter )
        {
            $sql->where .= " ( upper(m_transaction) like upper('{$filter}%') )";
        }
        $lookup->setGrid( 'base', $sql, $columns, 'Lookup Transactions', 15, 0 );
    }

    function lookupTransactionGroup(&$lookup)
    {
        $MIOLO = MIOLO::getInstance();
        
        $fTransaction = MUtil::NVL( $lookup->getFilterValue( 'filter0' ), $lookup->getFilterValue() );
        $fGroup = MUtil::NVL( $lookup->getFilterValue( 'filter1' ), '' );
        
        $objTransaction = $MIOLO->getBusiness( 'base', 'transaction' );
        $objQuery = $objTransaction->listAll();
        
        $lookup->addFilterField( new MSelection( 'filter0', $fTransaction, 'Transaction', $objQuery->result ) );
        $lookup->addFilterField( new MTextField( 'filter1', $fGroup, 'Group', 20 ) );
        $columns = array( 
                new MDataGridColumn( 'idtransaction', 'Id', 'right', true, '5%', true ), 
                new MDataGridColumn( 'm_transaction', 'Transaction', 'left', true, '40%', true ), 
                new MDataGridColumn( 'idgroup', 'IdGroup', 'left', true, '5%', true ), 
                new MDataGridColumn( 'm_group', 'Group', 'left', true, '40%', true ), 
                new MDataGridColumn( 'rights', 'Rights', 'left', true, '10%', true ) 
        );
        $sql = new sql( "t.idtransaction, t.m_transaction, g.idgroup, g.m_group, a.rights", "miolo_transaction t, miolo_access a, miolo_group g", "(t.idtransaction = a.idtransaction) and (a.idgroup = g.idgroup)", 't.m_transaction, g.m_group' );
        if ( $fGroup )
        {
            $sql->where .= " and ( upper(g.m_group) like upper('{$fGroup}%') )";
        }
        $sql->where .= " and ( t.idtransaction = '{$fTransaction}' )";
        $lookup->setGrid( 'base', $sql, $columns, 'Lookup Transaction/Groups', 15, 0 );
    }

    function lookupGroup(&$lookup)
    {
        $filter = $lookup->getFilterValue( 'filter' );
        if ( ! $filter )
            $filter = $lookup->getFilterValue();
        $lookup->addFilterField( new TextField( 'filter', $filter, 'Grupo', 20 ) );
        $columns = array( 
                new DataGridColumn( 'idgrupo', 'Id', 'right', true, '10%', true ), 
                new DataGridColumn( 'grupo', 'Grupo', 'left', true, '90%', true ) 
        );
        $sql = new sql( 'idgrupo, grupo', 'cm_grupoacesso', '', 'idgrupo' );
        if ( $filter )
        {
            $sql->where .= " ( upper(grupo) like
            upper('{$filter}%') )";
        }
        $lookup->setGrid( 'base', $sql, $columns, 'Pesquisa Grupos', 15, 0 );
    }

    public function autoCompleteGroup($value)
    {
        $MIOLO = MIOLO::getInstance();
        
        $db = $MIOLO->getDatabase( 'base' );
        
        $sql = "select m_group from miolo_group where idgroup = ?";
        
        return $sql;
    }

    public function autoCompleteUser($value)
    {
        $MIOLO = MIOLO::getInstance();
        
        $db = $MIOLO->getDatabase( 'base' );
        
        $sql = "select login from miolo_user  where iduser = ?";
        
        return $sql;
    }
}
?>
