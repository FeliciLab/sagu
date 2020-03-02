<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 * 
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 * 
 * Operation form
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 04/08/2008
 *
 **/


/**
 * Form to manipulate a preference
 **/
class FrmOperation extends GForm
{

    function __construct()
    {
        $this->setAllFunctions('Operation', null, array('operationId'), array('operationId', 'description') );
        parent::__construct();
    }

    /**
     * Default method to define fields
     **/
    public function mainFields()
    {
        if ( $this->function != 'insert' )
        {
            $fields[] = new MTextField('operationId', $this->operationId->value, _M('Código da operação',$this->module), 8, null, null, true);
            $validators[] = new MRequiredValidator('operationId');
        }

        $fields[] = $description = new MTextField('description', $this->description->value, _M('Descrição',$this->module), 20);
        //FIXME, problema decorrente de quando só existe um único textfield no form.
        $description->setAttribute('onpressenter', GUtil::getCloseAction(true));

        $fields[] = $defineRule = new GRadioButtonGroup('defineRule', _M('Define regra', $this->module), GUtil::listYesNo(1), 'f', null, MFormControl::LAYOUT_HORIZONTAL);

        $radios = $defineRule->mRadioButtonGroup->getControls();
        
        $radios[0]->addAttribute('onpressenter', GUtil::getCloseAction( true ) );
        $radios[1]->addAttribute('onpressenter', GUtil::getCloseAction( true ) );
        
        $this->setFields($fields);

        $validators[] = new MRequiredValidator('description');
        
        $this->setValidators($validators);
    }
}
?>
