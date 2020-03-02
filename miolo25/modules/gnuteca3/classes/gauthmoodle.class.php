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
 * Class GDate
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 17/06/2011
 *
 **/

class gAuthMoodle extends MAuth
{
    private $baseId;
    
    public function __construct($baseId = null) 
    {
        if ( MY_LIBRARY_AUTHENTICATE_TYPE != BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN )
        {
            throw new Exception (_M('A autenticação deve ser via campo Login. Avise o administrador.','gnuteca3'));
        }

        parent::__construct();
    }
    
    /**
     * Método privado para autenticar na base moodle
     * @return (object) conexão 
     */
    private function connect()
    {
        try
        {
            return $this->manager->getDatabase('moodle');
        }
        catch ( Exception $e )
        {
            return false;
        }
    }
    
    /**
     * Método de autenticação do moodle
     * 
     * @param (String) $login
     * @param (String) $password
     * @return boolean, true se autenticou 
     */
    public function authenticate($login, $password=null)
    {
        $MIOLO = MIOLO::getInstance();
        
        try
        {
                if (!$login )
            	{
                    return false;
            	}

                $conn = $this->connect(); //conecta na base

                if ( $conn ) //Se conectou-se ao moodle
                {
                    $busPerson = $MIOLO->getBusiness('gnuteca3', 'BusPerson');
                    //Verifica se autenticou usuário e senha
                    $personData = $this->getPersonByLogin($login);

                    //Tenta autenticar direto no Gnuteca sem SALT
                    $personAuth = $busPerson->authenticate($login,$password);

                    //Se nao tiver autenticado, verifica senha com SALT
                    
                    if ( empty($personAuth) ) 
                    {
                        $personAuth = $busPerson->authenticate($login,$password . $MIOLO->getConf('login.saltKey'));
                    }
                    
                    if ( !empty($personAuth) ) 
                    {
                        return $personAuth;
                    }
                    else //Se nao autenticou direto no gnuteca 
                    {
                        //Tenta sincronizar informacoes do moodle com o gnuteca.
                        $syncPersonId = $this->synchronizePersonFromMoodle($login);
                        
                        if ( !$syncPersonId )
                        {
                            $person = $busPerson->getPerson($login);
                            
                            if ( $person )
                            {
                                $login = $person->login;
                                $syncPersonId = $this->synchronizePersonFromMoodle($login);
                            }
                        }

                        if ( $syncPersonId )
                        {
                            $login = $syncPersonId;
                            
                            //Apos a sincronia tenta autenticar sem SALT
                            $personAuth = $busPerson->authenticate($login,$password);

                            //Se nao autenticar
                            if ( empty($personAuth) ) 
                            {
                                //Tenta autenticar com SALT.
                                $personAuth = $busPerson->authenticate($login,$password . $MIOLO->getConf('login.saltKey'));
                            }                            
                            
                            return $personAuth;
                        }
                        else 
                        {
                            clog('problem sincronia');
                        }
                    }
                }
                else
                {
                    return false;
                }
        }
        catch ( Exception $e )
        {
            return false;
        }
    }
    
   
    /**
     * 
     * @param string $username o login
     */
    public function getPersonFromMoodle($username)
    {
        
        try 
        {
            $dbMoodle = $this->manager->getDatabase('moodle');
            //Seleciona dados da pessoa
            $moodlePerson = $dbMoodle->query("SELECT firstname,lastname,email,phone1,phone2,city,address,username,password FROM mdl_user WHERE username = '$username';",0, NULL, MQuery::FETCH_OBJ)->result[0];
            
            //Se veio informaçoes da pessoa.
            if ( !empty($moodlePerson->username) )
            {
                //retorna o objeto com os dados dela que estava dentro do moodle.
                return $moodlePerson;
            }
            
            return false;
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    
    public function synchronizePersonFromMoodle($login)
    {
        $MIOLO = MIOLO::getInstance();

        //Obtem pessoa do gnuteca.
        $personData = $this->getPersonByLogin($login);

        $busPerson = $MIOLO->getBusiness('gnuteca3', 'BusPerson');
        
        //Se pessoa existir no moodle
        $moodlePerson = $this->getPersonFromMoodle($login);
        
        if ( $moodlePerson )
        {
            //Atualiza os dados do moodle sobre os dados do Gnuteca.
            $personData->personName = $moodlePerson->firstname .' '. $moodlePerson->lastname;
            $personData->email = $moodlePerson->email;
            $personData->locationTypeId = 35; //TODO Entender porque no gnuteca este campo da basperson é obrigatório. Vide ticket
            $personData->location = $moodlePerson->address;
            $personData->login = $moodlePerson->username;
            $personData->password = $moodlePerson->password;

            $bond = new stdClass();
            $bond->linkId = 1;        
            $personData->bond = array($bond);

            //seta o vínculo da pessoa
            $phone1 = new stdClass();
            $phone1->type = 'RES';//Todo: Vinculo tem que saber como vem.
            $phone1->phone = $moodlePerson->phone1;
            $phone2 = new stdClass();
            $phone2->type = 'RES';//Todo: Vinculo tem que saber como vem.
            $phone2->phone = $moodlePerson->phone2;                

            //Existem so dois telefones pra vir do moodle.
            $personData->phone = array($phone1,$phone2);        
            $busPerson->setData($personData);

            //Se a pessoa existe no Gnuteca.
            if ( $personData->personid )
            {
                //Se nao conseguiu atualizar a pessoa.
                if( !$busPerson->updatePerson() ) 
                {
                    return false;
                }                
            }
            else //Se a pessoa nao existe no gnuteca
            {

                //Se nao conseguiu inserir a pessoa do moodle.
                if( !$busPerson->insertPerson() ) 
                {
                    return false;
                }                           
            }

            //Retorna o codigo da pessoa para tentar autenticar.
            return $busPerson->personId;
        }
        else
        {
            return false;
        }
    }
    
    
    public function getPersonByLogin($login)
    {
        $MIOLO = MIOLO::getInstance();
        
        $busPerson = $MIOLO->getBusiness('gnuteca3', 'BusPerson');
        $busPerson->loginS = $login;
        $personData = $busPerson->searchPerson(true);
        return $personData[0];
    }
}
?>
