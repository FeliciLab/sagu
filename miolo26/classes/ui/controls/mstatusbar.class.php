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
// | Abstract: This file contains the statusbar elements definitions |
// |                                                                 |
// | Created: 2001/08/14 Vilson Cristiano GÃ¤rtner,                   |
// |                     Thomas Spriestersbach                       |
// |                                                                 |
// | History: Initial Revision                                       |
// +-----------------------------------------------------------------+

class MStatusBar extends MControl
{
    public $cols;

    public function __construct($cols = null)
    {
        parent::__construct();
//        $this->addStyleFile('m_themeelement.css');
        $this->cols = $cols;

        $login = $this->manager->getLogin();

        if ($login)
        {
            $online = (time() - $login->time) / 60;
            $this->addInfo(_M("User") . ' ' . $login->id);
            $this->addInfo(_M("Login at") . ' ' . Date('H:i', $login->time) . ' (' . sprintf('%02d:%02d', $online / 60, $online % 60) . ')');
            $this->addInfo(_M("Date") . ' ' . Date('d/m/Y', $login->time));
        }
        else
        {
            $this->addInfo(_M("User") . ' : -');
            $this->addInfo(_M("Login at") . ' : --:--');
            $this->addInfo(_M("Date") . ' : --/--/----');
        }

        $this->addInfo(MIOLO_VERSION);
        $this->addInfo(MIOLO_AUTHOR);
    }

    public function addInfo($info)
    {
        $this->cols[] = $info;
    }

    public function clear()
    {
        unset($this->cols);
    }

    public function generate()
    {
        $ul = new MUnOrderedList();
        $ul->addOptions($this->cols);
        $div = new MDiv('', $ul, 'mStatusBar');
        return $div->generate();
    }
}

?>