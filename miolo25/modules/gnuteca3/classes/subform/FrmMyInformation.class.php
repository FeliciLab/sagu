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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 20/01/2009
 *
 **/
class FrmMyInformation extends GSubForm
{
    public $MIOLO;
    public $module;
    public $busAthenticate;
    public $busBond;
    public $busPerson;
    public $busUserGroup;
    public $busPhone;
    public $busDomain;

    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->busBond         = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->busAthenticate  = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $this->busPerson       = $this->MIOLO->getBusiness($this->module, 'BusPerson');
        $this->busUserGroup    = $this->MIOLO->getBusiness($this->module, 'BusUserGroup');
        $this->busPhone        = $this->MIOLO->getBusiness($this->module, 'BusPhone');
        $this->busDomain       = $this->MIOLO->getBusiness($this->module, 'BusDomain');
        parent::__construct( _M('Dados pessoais', $this->module) );
    }

    public function createFields()
    {
        //Mensagem a ser mostrada no topo da tela
        $fields[] = new MDiv('', LABEL_PERSON_DATA);
        $fields[] = $this->getGrid();

        $this->defaultButton = false;
        $this->setFields($fields);
    }

    public function getGrid()
    {
        $personId   = BusinessGnuteca3BusAuthenticate::getUserCode();
        $personLink = $this->busBond->getPersonLink($personId);

        /**
        * monta o grupo de menor nível do usuário e acrescenta a validade para mostrar na linha Bond do array abaixo
        **/
        $bond = $personLink->description;
        $validade = $personLink->dateValidate;
        $date = GDate::construct($validade)->getDate(GDate::MASK_DATE_USER);
        $link = $this->busUserGroup->getUserGroup($personLink->linkId);

		if ( MUtil::getBooleanValue( $link->isVisibleToPerson ) )
		{
            $labelBond = _M("Vínculo", 'gnuteca3');

            //Se tiver data de validade do vinculo diz até quanto dura
            if ( $validade )
            {
                $bondVisible = $bond . ', válido até: ' . $date;
            }
            else
            {
                //Se não tiver data de validade do vinculo diz que é permanente
                $bondVisible = $bond . ', permanente';
            }
		}

        $person = $this->busPerson->getPerson($personId, TRUE);
        $group  = $this->busBond->getBond($personId, TRUE);

        $this->busPhone->personIdS = $personId;
        $phone = $this->busPhone->searchPhone(true);
        $phoneDesc = $this->busDomain->listDomain('TIPO_DE_TELEFONE', false, true);
        
        //gera a string com os telefones
        $numbers = '';
        if ( is_array($phone) )
        {
        	foreach ($phone as $value)
        	{
        	   $numbers .= $phoneDesc[$value->type] . ': ' . $value->phone . '<br>' ;   	
        	}
        }

        $data = array(
            array(_M('Nome', $this->module), $person->personName),
            array(_M('Endereço', $this->module), $person->location),
            array(_M('Número', $this->module), $person->number),
            array(_M('Complemento', $this->module), $person->complement),
            array(_M('Cidade', $this->module), $person->cityName),
            array(_M('CEP', $this->module), $person->zipCode),
            array(_M('E-mail', $this->module), $person->email),
            array(_M('Telefone', $this->module), $numbers));

        if(!empty($labelBond)) 
        {  
           $data[] = array( $labelBond, $bondVisible);
        } 
        
        $data[] = array(_M('Foto', $this->module), GUtil::getPersonPhoto($person->personId, array('height'=>'120px') ) ); 

        $table = new MTableRaw('', $data, array( _M('Campo','gnuteca3'),_M('Informação','gnuteca3') ), 'personData');
        $table->addStyle('width','100%');
        
        return $table;
    }
}
?>
