<?php

class DMLCriteria extends PersistentCriteria
{
    protected $transaction;

    public function __construct($classMap, $manager)
    {
        parent::__construct($classMap, $manager);
        $this->transaction = NULL;
    }

    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
    }

    public function getTransaction()
    {
        return $this->transaction;
    }
}
?>