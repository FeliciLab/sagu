<?php
// +-----------------------------------------------------------------+
// | MIOLO - Miolo Development Team - UNIVATES Centro UniversitÃ¡rio  |
// +-----------------------------------------------------------------+
// | CopyLeft (L) 2001-2002 UNIVATES, Lajeado/RS - Brasil            |
// +-----------------------------------------------------------------+
// | Licensed under GPL: see COPYING.TXT or FSF at www.fsf.org for   |
// |                     further details                             |
// |                                                                 |
// | Site: http://miolo.codigolivre.org.br                           |
// | E-mail: vgartner@univates.br                                    |
// |         ts@interact2000.com.br                                  |
// +-----------------------------------------------------------------+
// | Abstract:                                                       |
// |                                                                 |
// | Created: 2001/08/14 Thomas Spriestersbach                       |
// |                     Vilson Cristiano GÃ¤rtner,                   |
// |                                                                 |
// | History: Initial Revision                                       |
// +-----------------------------------------------------------------+

/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MTree
{
    /**
     * Attribute Description.
     */
    public $root; // an array of arrays of tree nodes

    /**
     *
     */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function __construct()
    {
    }

    /**
     * It is assumed, that 'parent' node already exists
     */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $id (tipo) desc
     * @param $parent (tipo) desc
     * @param $data (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function addItem($id, $parent, $data)
    {
        if (!$parent)
            $parent = $this->root;

        $node = FindNode($this->root, $parent);
    }

    /**
     * Retrieves the 
     */
    public function &FindNode($parent, $id)
    {
        if (!$parent)
            return null;

        foreach ($parent as $key => $data)
        {
            if ($key == $id)
                return $data;

            $node = $this->findNode($data);

            if ($node)
                return $node;
        }

        return null;
    }

    /**
     *  Generates HTML tree
     */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function generate()
    {
    }
}
?>
