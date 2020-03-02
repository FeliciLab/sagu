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
 * Class gIso2709Record, extends the default gIso2709.
 *
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *         Jamiel Spezia [jamiel@solis.coop.br] 
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br] 
 *
 * @since
 * Class created on 31/08/2011
 *
 **/

$MIOLO = MIOLO::getInstance();
$MIOLO->getClass('gnuteca3', 'gIso2709');
class gIso2709Import extends gIso2709
{
    public $savedRecords = 0;
    
    /**
     * Executa a exportação dos materiais
     * @return boolean true se importou com sucesso 
     */
    public function execute()
    {
        $ok = array();
        
        if ( is_array($this->records) )
        {
            foreach( $this->records as $key => $record )
            {
                $controlNumber = $this->busPreCatalogue->getNextControlNumber();

                $tags = $record->getTags();

                if ( is_array($tags) && ($tags[0] instanceof stdClass) )
                {
                    foreach( $tags as $l => $tag )
                    {
                        if ( $this->checkIgnoreField($tag->fieldid, $tag->subfieldid, false) ) //ignora os campos que estão na preferência ISO2709_IMPORT
                        {
                            //trata as strings convertendo em UTF-8
                            $tag->content = GString::construct($tag->content);
                            
                           //caso for um campo de controle adiciona o valor "a" no subcampo
                           if ( self::isControlField($tag->fieldid) )
                           {
                               $tag->content->replace(' ', '#'); //troca o espaço pela hash "#"
                               $tag->subfieldid = 'a';
                           }
                           
                           $tag->content = addslashes($tag->content->getString()); //obtém a string e escapa os "'"

                           //trata os indicadores
                           $tag->indicator1 = GString::construct($tag->indicator1)->replace(' ', ''); //tira espaço do indicador 1
                           $tag->indicator1 = $tag->indicator1->getString(); //obtém string
                           $tag->indicator2 = GString::construct($tag->indicator2)->replace(' ', '');//tira espaço do indicador 2
                           $tag->indicator2 = $tag->indicator2->getString(); //obtém string

                           $this->busPreCatalogue->clean(); //limpa todos os atributos do business
                           $this->busPreCatalogue->setData($tag); //seta os dados no business
                           $this->busPreCatalogue->controlNumber = $controlNumber; //seta o número de controle
                           
                           $ok[] = $this->busPreCatalogue->insertMaterial(); //insere material na pré-catalogação
                        }
                    }
                    
                    $this->savedRecords++; //conta quando registros foram salvos
                }
            }
        }
        
        return !in_array(false, $ok );
       
    }
    
}

?>