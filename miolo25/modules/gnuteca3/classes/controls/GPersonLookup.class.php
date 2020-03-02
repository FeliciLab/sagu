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
 * Class
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 22/07/2011
 *
 **/

class GPersonLookup extends GContainer
{
    public $personLabel; //não mudar o nome do atributo para "label", pois não funciona no formulário o atributo vem vazio, em alguma classe pai o atributo é limpado
    
    private $lookup;
    
    //FIXME: permitir passar controls ou mudar o id do campo descrição
    function __construct ($fieldId = 'personId', $label = 'Pessoa', $lookup = 'activePerson')
    {
        $module = 'gnuteca3';
       
        //label do lookup
        $fields[] = $this->personLabel = new MLabel( $label . ':' );
        $this->personLabel->setWidth(FIELD_LABEL_SIZE);

        //campo lookup
        $fields[] = $this->lookup = new GPersonLookupTextField($fieldId, '', '', FIELD_LOOKUPFIELD_SIZE, NULL, NULL, 'personIdDescription,linkId, linkDesc', $module, $lookup);
        
        //descrição do lookup
        $fields[] = $personIdDesc = new MTextField('personIdDescription', null, NULL, FIELD_DESCRIPTION_LOOKUP_SIZE);
        $personIdDesc->setReadOnly(true);
        
        //string de campos relacionados
        $related =  $fieldId . ',personIdDescription';
        
        //adiciona campos especificos somente quando for um lookup activePerson
        if ( $lookup == 'activePerson' )
        {
            $related .= ',linkId,linkDesc'; //campos relacionados do activePerson
            
            //demais campos relacionados
            $fields[] = new MHiddenField('linkId');
            $fields[] = $linkDesc = new MTextField('linkDesc');
            $linkDesc->setReadOnly(true);
        }
        
        //seta o contexto para o lookup (ficou em baixo pois precisa montar a string de campos relacionados $related)
        $this->lookup->setContext($module, $module, $lookup, 'filler', $related, '', true); 
      
        parent::__construct('pContainer_' . $fieldId, $fields);
    }
    
    /**
     * Seta o lookup de pessoa como readOnly
     * @param boolean $readOnly para setar o lookup interno como somente leitura 
     */
    public function setReadOnly($readOnly = false)
    {
        $this->lookup->setReadOnly($readOnly);
    }
}

class GPersonLookupTextField extends GLookupTextField
{
    public function setValue($value)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        if ( strlen($value) > 0 && ( MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE || MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN ) )
        {
            $business = $MIOLO->getBusiness($module, 'BusPerson');
            $person = $business->getPerson($value);
            $value = $person->login;
        }

        parent::setValue($value);
    }
}
?>