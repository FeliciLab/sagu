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
 * FormatBackOfBook form
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
 * Class created on 04/11/2008
 *
 **/
class FrmFormatBackOfBook extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('FormatBackOfBook', null, 'formatBackOfBookId', 'description');
        parent::__construct();
    }


    public function mainFields()
    {
        if ($this->function == 'update')
        {
            $fields[] = new MTextField('formatBackOfBookId', null, _M('Código', $this->module), FIELD_ID_SIZE, null, null, true);
        }
        
        $fields[] = new MTextField('description', null, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        $validators[] = new MRequiredValidator('description');
        $lblFormat = new MLabel( _M('Formato', $this->module) . ':' );
        $lblFormat->setWidth(FIELD_LABEL_SIZE);
        $format   = new MMultiLIneField('format', null, null, null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $btnHelp  = new MButton('btnHelp', _M('Ajuda', $this->module), ':showFunctionHelp');
        $fields[] = new GContainer('hctFormat', array($lblFormat, $format, $btnHelp));
        $validators[] = new MRequiredValidator('format', _M('Formato', $this->module));
        $fields[] = new MMultiLIneField('internalFormat', null, _M('Formato interno', $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);

        $this->setFields($fields);
        $this->setValidators($validators);
    }


    public function showFunctionHelp()
    {
    	parent::_showFunctionHelp();
    }
}
?>
