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

class frmRccMensagensPorAssunto extends frmDinamico
{
    public $fonts;

    public function __construct()
    {
        parent::__construct(FALSE, 'Mensagens por assunto');
    }

    public function definirCampos()
    {
        parent::definirCampos(TRUE, TRUE);
    }

    public function createFields()
    {
        $tipo = bTipo::instanciarTipo('acdperiod', 'relcliente');
        $module = MIOLO::getCurrentModule();
        
        $fields[] = new MDiv('messageDiv');
        $fields[] = new MLabel(_M('Período em relação ao qual deseja obter as estatísticas', $module), '', true);
        $fields[] = new MSelection('selectPeriodo', null, null, $tipo->buscarParaSelection(null, 'periodid, description'));
        $fields[] = new MSpacer('70px');       
        
        $fields[] = new MLabel(_M('Número de mensagens por assunto em um período', $module), '', true);
        $fields[] = new MButton('btnAssunto', _M('Gerar Relatório', $module));
        $fields[] = new MSpacer('70px');
        
        $this->setFields($fields);
        $this->defaultButton = false;
    }
    
    public function btnAssunto_click($args)
    {
        $parameters = array();
        $parameters['periodid'] = $args->selectPeriodo;
        $report = new MJasperReport('relcliente');
        $created = $report->execute('relcliente', 'mensagensPorAssunto', $parameters);

        if($created == 0 )
        {
            new MMessageWarning(_M('Relatório não pode ser gerado.'));
        }
    }

}

?>