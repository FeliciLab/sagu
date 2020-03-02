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
 * Componente adminsitrador de dicionários, é um lookup especializado
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 10/03/2010
 *
 **/
class GDictionaryField extends MLookupTextField
{
    public function  __construct( $name = '', $value = '', $label = '', $size = FIELD_DESCRIPTION_SIZE, $hint = '', $validator = null, $related = '', $module = '', $item = '', $event = 'filler', $filter = '', $autocomplete = true )
    {
        parent::__construct( $name, $value, $label, $size, $hint, $validator, $related, $module, $item, $event, $filter, $autocomplete );
        $this->autocomplete = false;
        $this->baseModule = $this->module;
        $this->page->addScript('GDictionaryField.js', 'gnuteca3');
    }

    public function generate()
    {
        $readonly = $this->getAttribute( 'readonly' );

        if ( !$readonly )
        {
            $delaySeach = DICTIONARY_DELAY_SEARCH;
            $this->addAttribute('onkeydown', "return onkeyUpDictionary(event, this,'{$this->name}','{$this->related}', '{$this->item}', '{$this->filter}','{$delaySeach}');" ); //ajax
            $this->addAttribute('onblur',"return onblurDictionary('{$this->name}');");
            $this->addAttribute('ondblclick',"return onDoubleClick(event,'{$this->name}')");

            $div = "<div id='{$this->name}Div' class='mTextField gDictionary'></div>";
            $input = "<input type='hidden' id='{$this->name}Input' value=''/>"; //somente id para não ir no post
        }

        return parent::generate().$div.$input;
    }

    /**
     * Função chamada após a digitação dos dados
     * 
     * @param stdClass $args
     */
    public static function onkeyUpDictionary($args)
    {
        $MIOLO = MIOLO::getInstance();
        $name = $args->name;
        $div = $name.'Div';
        $value = $args->$name;
        $lookup = $args->item;
        $related = $args->related;
        $filter = $args->filter;

        $dbLookup = $MIOLO->getBusiness('gnuteca3', 'lookup');
        $dbLookup = new BusinessGnuteca3Lookup();
        $dbLookup->setForRepetitiveField(true,true); //seta função lookup para repetitive field

        //instancia o lookup chamado e executa a função especifica
        if ( $lookup ) //se esse id tiver lookup
        {
            $function   = 'autoComplete'.$lookup; //monta função de autocomplete

            if ( method_exists( $dbLookup, $function ) ) //verifica se função existe
            {
                $_REQUEST['filter'] = $value; //seta o filter
                $dbLookup->$function(); //chama a função do lookup
                $result = $dbLookup->result;
            }
        }

        // Trata os dados.
        if ( $result )
        {
            $onClick = array();
            
            foreach ( $result as $line => $info)
            {
                // Escapa string.
                $valueScaped = str_replace("'", "\'", $info[0] ); 
                $onClick[$line] = 'dojo.byId(\''.$name.'\').value = \''.$valueScaped.'\'; dojo.byId(\''.$div.'\').style.display=\'none\';';
                $result[$line] = $info[0];
            }
        }

        //cria uma tabela com os dados
        $content = $table = new MTableRaw( null, $result , null);
        
        // Adiciona o evento para cada linha e célula da tabela. Garantindo o disparo do evento.
        foreach( $result as $i => $value )
        {
            $table->setCellAttribute($i, 0, 'onClick', $onClick[$i]);
            $table->setCellAttribute($i, 0, 'id', "{$name}Item{$i}");
            $table->setRowAttribute($i, 'onClick', $onClick[$i]);
        }
        
        $table->setAlternate( true );
        $table->addAttribute('style','width:100%');
        $table->addAttribute('id',$name.'Table');
        
        //só executa o js que mostra a caixa caso tenha resultados
        if ( $result )
        {
            $MIOLO->page->onload( "onkeyUpResponse('$name');" );
        }
        else
        {
            $MIOLO->page->onload("dojo.byId('{$name}Div').style.display='none';");
        }
        
        $MIOLO->ajax->setResponse( $content, $div);
    }
}
?>
