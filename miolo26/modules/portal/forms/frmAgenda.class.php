<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/09/11
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */

$MIOLO->uses('/ui/controls/meventcalendar.class.php');
$MIOLO->uses('types/BasReports.class', 'basic');
$MIOLO->uses('forms/frmMobile.class.php', $module);

class frmAgenda extends frmMobile
{
    
    public $js;
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Agenda', MIOLO::getCurrentModule()));

        $this->eventHandler();
        $this->setShowPostButton(FALSE);
    }

    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = SAGU::getFileModule(__FILE__);

        // Obtém os dados do form
        $personId = $this->personid;
        $beginDate = '01/01/'.date('Y');
        $endDate = '31/10/'.date('Y');
        
	// Instancia os business
        $busPhysicalPerson = $MIOLO->getBusiness('basic', 'BusPhysicalPerson');
        // Obtém os dados da pessoa
        $person = $busPhysicalPerson->getPhysicalPerson($personId);

        // Parâmetros para fazer a filtragem de eventos
        $opts = array();
        $opts['personId'] = $personId;
        $opts['showScheduledActivity'] = DB_TRUE;

        // Monta o calendário acadêmico
        $rows = BasReports::getCalendarEvent($beginDate, $endDate, $opts);
        $total = array();
        $calendar = new MEventCalendar('calendar', htmlentities(_M('Agenda pessoal de @1', 'basic', $person->name)));

        foreach ( (array)$rows as $row )
        {
            if ( !isset($total[$row->month]) )
            {
                $total[$row->month] = 0;
            }

            if ( strlen($row->events) > 0 )
            {
                foreach ( explode("\n", $row->events) as $event )
                {
                    $total[$row->month]++;
                    $calendar->defineEvent($row->dateCalendar, new BString($event));
                }
            }
        }

        //if ( array_sum($total) > 0 )
        {
            // Gera totais
            $array = array();
            $months = SAGU::listMonths();
            foreach ( (array)$months as $month => $name )
            {
                $array[] = $total[$month];
            }
            $label = _M('Total de eventos encontrados: @1', 'basic', array_sum($total));
            $table = new MTableRaw($label, array($array), array_values($months));
            for ( $i = 0; $i < count($months); $i++ )
            {
                $table->setCellAttribute(0, $i, 'align', 'center');
            }

            $fields[] = new MSeparator();
            $fields[] = $table;
            $table->setWidth('99%');
            $fields[] = new MSeparator();
            $fields[] = $calendar;
        }
        //else
        {
            //$this->AddAlert(_M('Nenhum evento encontrado para o período e curso informado.', 'basic'));
        }
        
        $div = new MDiv('',$fields);
        $div->addStyle('width', '100%');
        
        $this->addJsCode("$('#__mainForm').trigger('create')");
        
	parent::addFields(array($div));
    }

    public function salvar($args)
    {   
        $this->setResponse(NULL, 'responseDiv');
    }

}

?>
