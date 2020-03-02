<?php
/**
 * @author Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @since
 * Class created on 29/12/2012
 *
 **/

$MIOLO->uses('classes/telaRespostaOuvidoria.class.php', 'relcliente');
$MIOLO->uses('forms/frmDinamico.class.php', 'base');

class frmRccRelatorios extends frmDinamico
{
    public $fonts;

    public function __construct()
    {
        parent::__construct(FALSE, 'Relatórios');
    }

    public function definirCampos()
    {
        parent::definirCampos(FALSE);
    }

    public function createFields()
    {
        $tipo = bTipo::instanciarTipo('acdlearningperiod', 'relcliente');
        $module = MIOLO::getCurrentModule();
        
        $fields[] = new MLabel(_M('Período em relação ao qual deseja obter as estatísticas', $module), '', true);
        $fields[] = new MSelection('selectPeriodo', null, null, $tipo->buscarParaSelection(null, 'periodid, description'));
        $fields[] = new MSpacer('70px');

        $fields[] = new MLabel(_M('Número de mensagens por tipo de contato em um período', $module), '', true);
        $fields[] = new MButton('btnTipo', _M('Gerar Relatório', $module));
        $fields[] = new MSpacer('70px');
        
        $fields[] = new MLabel(_M('Número de mensagens por vínculo em um período', $module), '', true);
        $fields[] = new MButton('btnVinculo', _M('Gerar Relatório', $module));
        $fields[] = new MSpacer('70px');        
        
        $fields[] = new MLabel(_M('Número de mensagens por assunto em um período', $module), '', true);
        $fields[] = new MButton('btnAssunto', _M('Gerar Relatório', $module));
        $fields[] = new MSpacer('70px');
        
        $fields[] = new MLabel(_M('Número de mensagens respondidas em um período', $module), '', true);
        $fields[] = new MButton('btnRespondidas', _M('Gerar Relatório', $module));
        $fields[] = new MSpacer('70px');

        $this->setFields($fields);
        $this->defaultButton = false;
    } 
    
    public function btnTipo_click($args)
    {
        $parameters = array();
        $parameters['periodid'] = $args->selectPeriodo;
        $report = new MJasperReport('relcliente');
        $report->execute('relcliente', 'mensagensPorTipo', $parameters);
    }
    
    public function btnVinculo_click($args)
    {
        $parameters = array();
        $parameters['periodid'] = $args->selectPeriodo;
        $report = new MJasperReport('relcliente');
        $report->execute('relcliente', 'mensagensPorVinculo', $parameters);
    }    
    
    public function btnAssunto_click($args)
    {
        $parameters = array();
        $parameters['periodid'] = $args->selectPeriodo;
        $report = new MJasperReport('relcliente');
        $report->execute('relcliente', 'mensagensPorAssunto', $parameters);
    }
    
    public function btnRespondidas_click($args)
    {
        $parameters = array();
        $parameters['periodid'] = $args->selectPeriodo;
        $report = new MJasperReport('relcliente');
        $report->execute('relcliente', 'mensagensRespondidas', $parameters);
    }

}

?>