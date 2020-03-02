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
 * Class convert string to marc21 format
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *         Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Fiegenbaum [jader@solis.coop.br]
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 21/10/2011
 *
 **/

$MIOLO->getClass('gnuteca3', 'gMarc21Record');
class GMarc21
{
    private $recordDelimiter;
    private $records = array();
    
    public function __construct($content, $fieldDelimiter = "\n", $subFieldDelimiter = '$', $emptyIndicator = '#', $recordDelimiter = '---')
    {
        $this->setRecordDelimiter($recordDelimiter); //seta o delimitador de registro
        $records = explode($recordDelimiter, $content);
        
       //percorre os registros e setado-os no objeto
        if ( is_array($records) )
        {
            foreach ( $records as $i => $record )
            {
                $this->setRecord( new gMarc21Record($record, $fieldDelimiter, $subFieldDelimiter, $emptyIndicator )); //adiciona um record
            }
        }
    }
    
    /**
     * Seta o delimitador de registro
     * 
     * @param String $recordDelimiter 
     */
    public function setRecordDelimiter($recordDelimiter)
    {
        $this->recordDelimiter = $recordDelimiter;
    }
    
    /**
     * Obtém o delimitador de registro
     * 
     * @return String delimitador 
     */
    public function getRecordDelimiter()
    {
        return $this->recordDelimiter;
    }
    
    /**
     * Adiciona um registro ao objeto
     * 
     * @param GMarc21Record $record 
     */
    public function setRecord(gMarc21Record $record)
    {
        $this->records[] = $record;
    }
    
    /**
     * Obtém um registro específico do objeto
     * 
     * @param int $i posição no atributo
     * @return GMarc21Record registro 
     */
    public function getRecord($i)
    {
        return $this->records[$i];
    }
    
    /**
     * Retorna os registros do objeto
     * 
     * @return array de GMarc21Record 
     */
    public function getRecords()
    {
        return $this->records;
    }
    
    /**
     * Obtém a quantidade de registros do objeto
     * 
     * @return int número 
     */
    public function getSize()
    {
        return count($this->records);
    }
    
}
?>