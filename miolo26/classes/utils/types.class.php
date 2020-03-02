<?php

// definition of QueryRange class
// Used by: PageNavigator  
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MQueryRange
{
    /**
     * Attribute Description.
     */
    public $offset;

    /**
     * Attribute Description.
     */
    public $rows;

    /**
     * Attribute Description.
     */
    public $total;

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $page (tipo) desc
     * @param $rows (tipo) desc
     * @param $total0 (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function __construct($page, $rows, $total = 0)
    {
        $this->offset = $page * $rows;
        $this->rows = $rows;
        $this->total = $total;
    }
}
?>
