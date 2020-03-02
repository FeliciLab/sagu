<?php
$MIOLO->getClass( 'gnuteca3', 'workflow/wfPurchaseRequest' );
class wfPurchaseRequestDefault extends wfPurchaseRequest
{
    public function  __construct($data)
    {
        parent::__construct($data);
    }

    public function initialize()
    {
        return parent::initialize();
    }

    public function aprove()
    {
        return parent::aprove();
    }

    public function cancel()
    {
        return parent::cancel();
    }

    public function purchaseRequest()
    {
        return parent::purchaseRequest();
    }

    public function finalize()
    {
        return parent::finalize();
    }
}
?>
