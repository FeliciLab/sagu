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
 *
 * @author Luiz Gilberto Gregory Fº
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
 * Class created on 01/12/2008
 *
 **/

class GOperator extends GBusiness
{

    function __construct()
    {
        parent::__construct();
        $this->setDb('gnuteca3');
    }

    /**
     * Retorna o ID do operador
     *
     * @return string
     */
    public static function getOperatorId()
    {
        $MIOLO    = MIOLO::getInstance();
        $module   = MIOLO::getCurrentModule();

        $id        = $MIOLO->getLogin()->id;
        return (!is_null($id) && strlen($id)) ? $id : "gnuteca3";
    }
    
    /**
     * Obtém nome do operador
     * 
     * @param String $id login do operador
     * @param boolean $shortName para abreviar o nome
     * @return string $nome Nome do operador
     */
    public static function getOperatorName($id, $shortName = true)
    {
        $MIOLO = MIOLO::getInstance();
        
        if( strlen($id) && $id != 'gnuteca3' )
        {
            $gOperator = new GOperator;
            $operator = $gOperator->searchOperator($id); //busca o operador pelo login
            $name = $operator[0][2]; //obtém o nome
            
            //testa se é necessário abreviar
            if ( $shortName )
            {
                $parts = explode(' ', $name); //gera um array quebrando por espaço
                $name = array();

                foreach ( $parts as $l => $part )
                {
                    if ( $l == 0 )
                    {
                        $name[] = $part; //o primeiro nome fica igual
                    }
                    else
                    {
                        $name[] = substr($part, 0, 1) . '.'; //obtém primeira letra dos sobrenomes e concatena "."
                    }
                }

                $name = implode(' ', $name); //gera uma string separada por espaço
            }
        }
        else
        {
            $name = 'gnuteca3'; //retorna gnuteca3
        }
        
        return $name;
    }

    /**
     * Verifica se tem um operador logado
     *
     * @return boolean
     */
    public static function isLogged()
    {
    	$MIOLO    = MIOLO::getInstance();
        $module   = MIOLO::getCurrentModule();

        $id         = $MIOLO->getLogin()->id;
        return ( !is_null($id) ? true : false) ;
    }

    /**
     * Verifica se o operador possui alguma pemissão no gnuteca
     *
     * @return boolean
     */
    public static function hasSomePermission()
    {
        return $_SESSION['login']->rights ? true : false;
    }

    /**
     * Listagem de operador obtidas da base admin do miolo.
     *
     * @return <type> lista de operadores
     */
    public function listOperator()
    {
        $this->setDb('admin');

        $this->clear();
        $this->setColumns('login, nickname');
        $this->setTables('miolo_user');
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }

    /**
     * Função estática para listagem de operadores, utilizada em relatórios.
     * @return <type> lista de operadores
     */
    public static function listOperators($allOperators = null)
    {
        $gOperator = new GOperator();
        $gOperator = $gOperator->listOperator();

        if ( ! is_null($allOperators) )
        {
            array_unshift($gOperator, array("Todos","Todos"));
        }
        
        return $gOperator;
    }

    /**
     * Retorna biblioteca do operador logada
     *
     * @return string id da iblioteca
     */
    public static function getLibraryUnitLogged()
    {
        return MIOLO::getInstance()->getLogin()->libraryUnitId;
    }

    /**
     * Retorna o nome da bibilioteca
     *
     * @return string nome da iblioteca
     */
    public static function getLibraryNameLogged()
    {
        return MIOLO::getInstance()->getLogin()->libraryName;
    }

    /**
     * Obtém os nomes dos operadores (do GNUTECA), foi reescrito para poder passar os id's por array
     *
     * @param array $id or int $id
     * @return array com relação login/nome
     */
    public function searchOperator($id = null, $name = null)
    {
        $this->setDb('admin');
        $this->clear();

        if ( $id )
        {
            if ( is_array($id) )
            {
                $id = implode("','", $id);
                $this->setWhere("A.login in ('{$id}')");
            }
            else
            {
                 $this->setWhere("A.login = '{$id}'");
            }
        }

        if ( $name )
        {
            $this->setWhere("lower(A.name) like lower('{$name}%')");
        }
        
        //restringe pelo módulo gnuteca
         $this->setWhere("C.idModule = 'gnuteca3'");
        
        $this->setColumns('distinct(A.iduser), A.login, A.name');
        $this->setTables('miolo_user A
              INNER JOIN miolo_groupuser B
                      ON (A.iduser = B.iduser)
              INNER JOIN miolo_group C
                       ON (B.idgroup = C.idgroup)');
        $sql = $this->select();

        return $this->query($sql);
    }
}
?>
