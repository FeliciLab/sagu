<?php
/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Solu��es Livres Ltda.
 *
 * Este arquivo � parte do programa Sagu.
 *
 * O Sagu � um software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * dentro dos termos da Licen�a P�blica Geral GNU como publicada pela Funda��o
 * do Software Livre (FSF); na vers�o 2 da Licen�a.
 *
 * Este programa � distribu�do na esperan�a que possa ser �til, mas SEM
 * NENHUMA GARANTIA; sem uma garantia impl�cita de ADEQUA��O a qualquer MERCADO
 * ou APLICA��O EM PARTICULAR. Veja a Licen�a P�blica Geral GNU/GPL em
 * portugu�s para maiores detalhes.
 *
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral GNU, sob o t�tulo
 * "LICENCA.txt", junto com este programa, se n�o, acesse o Portal do Software
 * P�blico Brasileiro no endere�o www.softwarepublico.gov.br ou escreva para a
 * Funda��o do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 * Classe gerenciadora de types
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Equipe Solis [sagu2@solis.coop.br]
 *
 * @since
 * Class created on 01/04/2011
 */

$MIOLO->uses('tipos/BasLog.class.php', 'base');

class SType extends BasLog
{
    /**
     * Atributo reservado MSubDetail
     *
     * @var <type>
     */
    protected $removeData;

    /**
     * Atributo que armazena itens ja populados sob-demanda,
     * util para ser utilizado no metodo __get() do type.
     */
    protected $_checkedPopulate = array();
    
    private $_aliases = array();

    
    public function __get($name)
    {
        $name = $this->checkName($name);

        return $this->$name;
    }


    public function __set($name, $value)
    {
        $name = $this->checkName($name);
        
        $this->$name = $value;
    }
    
    
    /**
     *
     * @param type $name
     * @return type 
     */
    private function checkName($name)
    {
        // Alias
        $alias = $this->_aliases[$name];
        if ( strlen($alias) > 0 )
        {
            $name = $alias;
        }
        
        return $name;
    }


    public function getObjectVars()
    {
        return get_object_vars($this);
    }
    
    
    /**
     * Define um pseudo atributo, que deve ser "redirecionado" para um outro atributo original.
     * Toda vez que for definido (Objeto->atributoAlias = 'valor') sera definido o valor para o atributo original.
     * Toda vez que for obtido (Objeto->atributoAlias) sera obtido o valor do atributo original.
     * Util para casos onde existam atributos na subdetail que sao diferentes do nome do atributo no SType.
     * 
     * Lembre-se que o pseudo atributo tambem deve estar declarado como protected na respectiva classe SType.
     * 
     * Exemplo pratico de uso na classe modules/training/types/TraTeam.class
     *
     * @param type $alias Pseudo nome de atributo
     * @param type $attribute Atributo fonte, que deve ser setado e obtido o valor
     */
    public function addAlias($alias, $attribute)
    {
        $this->_aliases[$alias] = $attribute;
    }


    /**
     * Verifica se deve popular dados sob demanda
     * Util para utilizar no __get() do type.
     *
     * CUIDADO: Ao utilizar esta funcao uma vez, o $name passado sera anotado como "ja populado"
     *
     * @param string $name
     */
    protected function needCheckPopulate($name)
    {
        $inArray = in_array($name, $this->_checkedPopulate);

        if ( !$inArray )
        {
            $this->_checkedPopulate[] = $name;
        }

        return ! $inArray;
    }
    
    /**
     * Obtem o nome da tabela, baseando-se no nome da classe por padrao.
     *
     * @return string
     */
    public function getTableName()
    {
        return get_class($this);
    }
    
    /**
     * Obtem ultimo id inserido
     */
    public function getLastInsertId()
    {
        return SDatabase::getLastInsertId($this->getTableName());
    }
    
    /**
     * Obtem nome da chave primaria
     */
    public function getPrimaryKey()
    {
        return SDatabase::getPrimaryKey($this->getTableName());
    }
}
?>