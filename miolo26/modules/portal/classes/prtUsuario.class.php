<?php

/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Solu��es Livres Ltda.
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
 * Usuario portal
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 * Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * @since
 * Class created on 17/09/2012
 *
 */

session_start();

$MIOLO->uses('types/AcpInscricao.class', 'pedagogico');

class prtUsuario extends MForm
{
    const USUARIO_ALUNO = 'A';
    const USUARIO_PROFESSOR = 'P';
    const USUARIO_COORDENADOR = 'C';
    const USUARIO_BASICO = 'B';
    const USUARIO_GESTOR = 'G';
    const TODOS_USUARIOS = 'T';

    public function __construct(){}

    /**
     *
     * @return boolean
     */
    public static function temMaisDeUmNivel()
    {
        return count(self::listaNiveisDeAcessos()) > 1;
    }
    
    public static function listaNiveisDeAcessos($personId)
    {
        $MIOLO = MIOLO::getInstance();
        $login = $MIOLO->getLogin();
        
        $niveis = array();
        
        if ( SAGU::getUsuarioLogado()->isStudent )
        {
            $niveis[self::USUARIO_ALUNO] = _M('Aluno');
        }
        
        if ( SAGU::getUsuarioLogado()->isProfessor )
        {
            $niveis[self::USUARIO_PROFESSOR] = _M('Professor');
        }
        
        if ( SAGU::getUsuarioLogado()->isCourseCoordinator )
        {
            $niveis[self::USUARIO_COORDENADOR] = _M('Coordenador');
        }
                
        if ( $login->groups['GESTOR'] == 'GESTOR' )
        {
            $niveis[self::USUARIO_GESTOR] = _M('Gestor');
        }

        $pedagogico = PrtUsuarioSagu::obterInscricoesAtivasDaPessoaTurmaGrupo($personId, true);
        
        if ( (prtUsuario::obterMultiplosContratos() || count($pedagogico) > 0 ) &&
             !SAGU::getUsuarioLogado()->isProfessor)
        {
            $contratos = PrtUsuarioSagu::obterContratosDaPessoa($personId);
            foreach( $contratos as $contrato )
            {
                $niveis[$contrato[0]] = _M('Aluno - ') . $contrato[1];
            }
        }
        
        if ( !count($niveis) > 0 )
        {
            $niveis[self::USUARIO_BASICO] = _M('Usuário básico');
        }
        
        return $niveis;
    }
    
    public function verificaProfessor($personid)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('types/PrtUsuarioSagu.class.php', $module);
        $usuarioSagu = new PrtusuarioSagu(208);
        
        return $usuarioSagu->verificaProfessor();
    }
    
    public function verificaAluno()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('types/PrtUsuarioSagu.class.php', $module);
        $usuarioSagu = new PrtusuarioSagu(208);
        
        return $usuarioSagu->verificaAluno();
    }
    
    public function verificaCoordenador()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('types/PrtUsuarioSagu.class.php', $module);
        $usuarioSagu = new PrtusuarioSagu(208);
        
        return $usuarioSagu->verificaCoordenador();
    }
    
    public function obtemUsuarioLogado()
    {
        $MIOLO = MIOLO::getInstance();
        return SAGU::getUsuarioLogado();
    }
    
    public static function definirContratoAtivo($contractId)
    {
        //Definiu contrato, mata a inscricao
        $_SESSION['contractId'] = $contractId;
        $_SESSION['inscricaoId'] = NULL;
    }
    
    public static function obterContratoAtivo()
    {
        return $_SESSION['contractId'];
    }
    
    public static function definirInscricaoAtiva($inscricaoTurmaGrupoId)
    {
        //Definiu inscricao, mata o contrato
        $_SESSION['inscricaoId'] = $inscricaoTurmaGrupoId;
        $_SESSION['contractId'] = NULL;
    }
    
    public static function obterInscricaoAtiva()
    {
        return $_SESSION['inscricaoId'];
    }
    
    public static function definirMultiplasInscricoes($multiplasInscricoes = TRUE)
    {
        $_SESSION['multiplasInscricoes'] = $multiplasInscricoes;
    }
    
    public static function obterMultiplasInscricoes()
    {
        return $_SESSION['multiplasInscricoes'];
    }
    
    public static function definirMultiplosContratos($multiplosContratos = TRUE)
    {
        $_SESSION['multiplosContratos'] = $multiplosContratos;
    }
    
    public static function obterMultiplosContratos()
    {
        return $_SESSION['multiplosContratos'];
    }

    public static function definirTipoDeAcesso($tipo)
    {
        $_SESSION['tipoDeAcesso'] = $tipo;
    }
    
    public static function obterTipoDeAcesso()
    {
        $MIOLO = MIOLO::getInstance();
        $login = $MIOLO->getLogin();
        
        if($_SESSION['tipoDeAcesso'])
        {
            return $_SESSION['tipoDeAcesso'];
        }
        else
        {
            if ( $login->groups['GESTOR'] == 'GESTOR' )
            {
                return self::USUARIO_GESTOR;
            }
            
            if ( SAGU::getUsuarioLogado()->isCourseCoordinator )
            {
                return self::USUARIO_COORDENADOR;
            }
            
            if ( SAGU::getUsuarioLogado()->isProfessor )
            {
                return self::USUARIO_PROFESSOR;
            }
            
            if ( SAGU::getUsuarioLogado()->isStudent )
            {
                $args = new stdClass();
                $args->personId = SAGU::getUsuarioLogado()->personId;
                
                $busContract = new BusinessAcademicBusContract();
                $contract = $busContract->searchContract($args);
                
                if ( count($contract) > 0 )
                {
                    return self::USUARIO_ALUNO;
                }
                else
                {
                    $possuiInscricao = AcpInscricao::possuiInscricao($args->personId);
                    
                    if ( $possuiInscricao )
                    {
                        return self::USUARIO_ALUNO;
                    }
                    else
                    {
                        return self::USUARIO_BASICO;
                    }
                }
            }
        }
    }
    
    public static function obterDisciplinasDoProfessor($professorId)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getBusiness('academic', 'BusScheduleProfessor');
        $busScheduleProfessor = new BusinessAcademicBusScheduleProfessor();
        
        return $busScheduleProfessor->obterDisciplinasDoProfessor($professorId);
    }
    
    public static function obterDisciplinasDoContrato($contractId)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getBusiness('academic', 'BusEnroll');
        $busEnroll = new BusinessAcademicBusEnroll();
        
        return $busEnroll->obterDisciplinasDoContrato($contractId);
    }
    
    public static function obterDadosPerfil($personId)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/BasPhone.class.php', 'basic');
        
        $busPerson = $MIOLO->getBusiness('basic', 'BusPerson');
        $busDocument = $MIOLO->getBusiness('basic', 'BusDocument');
        $busCity = $MIOLO->getBusiness('basic', 'BusCity');
        
        $person = $busPerson->getPerson($personId);
        
        $idCpf = SAGU::getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF');
        $cpf = $busDocument->getDocument($personId, $idCpf);
        $idRG = SAGU::getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_RG');
        $rg = $busDocument->getDocument($personId, $idRG);
                
        $phoneRes = BasPhone::getPhone($personId, 'RES');
        $phoneCel = BasPhone::getPhone($personId, 'CEL');
        
        $city = $busCity->getCity($person->cityId);
        
        $dados = new stdClass();
        $dados->nome = $person->name;
        $dados->email = $person->email;
        $dados->rg = $rg->content;
        $dados->cpf = $cpf->content;
        $dados->foneResidencial = $phoneRes;
        $dados->foneCelular = $phoneCel;
        $dados->endereco = $person->location;
        $dados->numero = $person->number;
        $dados->cep = $person->zipCode;
        $dados->complemento = $person->complement;
        $dados->cidade = $city->name;
        
        return $dados;
    }
    
    public function salvarDadosPerfil($dados)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/BasPhone.class.php', 'basic');
        
        $busPerson = $MIOLO->getBusiness('basic', 'BusPhysicalPerson');
        $busDocument = $MIOLO->getBusiness('basic', 'BusDocument');
        //$busCity = $MIOLO->getBusiness('basic', 'BusCity');
        
        $personData = new stdClass();
        $personData->name = $dados->nome ? $dados->nome : $dados->personName;
        $personData->zipCode = $dados->cep ? $dados->cep : $dados->zipCode;
        $personData->location = $dados->endereco;
        $personData->number = $dados->number;
        $personData->complement = $dados->complement;
        $personData->personId = $dados->personId;
        $personData->location = $dados->location;
        $personData->neighborhood = $dados->neighborhood;
        $personData->locationTypeId = $dados->locationTypeId;
        $personData->maritalStatusId = $dados->maritalStatusId;
        $personData->email = $dados->email;
        $personData->specialNecessityId = $dados->specialNecessityId;
        $personData->specialNecessityDescription = $dados->specialNecessityDescription;
        $personData->cityid = $dados->cityId;

        $ok = $busPerson->updatePhysicalPerson($personData, FALSE);
        
        $dados->cpf = $dados->cpf ? $dados->cpf : $dados->CPF;
        $idCpf = SAGU::getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF');
        $cpf = $busDocument->getDocument($dados->personId, $idCpf);
        if ( ($dados->cpf != $cpf->content) && (strlen($dados->cpf) > 0) )
        {
            $cpfData = new stdClass();
            $cpfData->personId = $dados->personId;
            $cpfData->documentTypeId = $idCpf;
            $cpfData->content = $dados->cpf;
            $busDocument->updateDocument($cpfData);
        }
        
        $idRG = SAGU::getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_RG');
        $rg = $busDocument->getDocument($dados->personId, $idRG);
        if ( $dados->rg != $rg->content || $dados->rgOrgao != $rg->organ)
        {
            $rgData = new stdClass();
            $rgData->personId = $dados->personId;
            $rgData->documentTypeId = $idRG;
            $rgData->content = $dados->rg;
            
            if ( strlen($dados->rgOrgao) > 0 )
            {
                $rgData->organ = $dados->rgOrgao;
            }
            $busDocument->updateDocument($rgData);
        }
        
        $phoneRes = BasPhone::getPhone($dados->personId, 'RES');
        $phoneCel = BasPhone::getPhone($dados->personId, 'CEL');
        
        $dados->residencial = strlen($dados->residencial) > 0 ? $dados->residencial : $dados->residentialPhone;        
        if ( $dados->residencial != $phoneRes )
        {
            if ( $phoneRes == NULL )
            {
                $phoneData = new stdClass();
                $phoneData->phone = $dados->residencial;
                $phoneData->personId = $dados->personId;
                $phoneData->type = 'RES';
                BasPhone::insertPhone($phoneData);
            }
            else
            {
                $phoneData = new stdClass();
                $phoneData->phone = $dados->residencial;
                $phoneData->personId = $dados->personId;
                $phoneData->type = 'RES';
                BasPhone::updatePhone($phoneData);
            }
        }
                
        $dados->celular = $dados->celular ? $dados->celular : $dados->cellPhone;
        if ( $dados->celular != $phoneCel )
        {
            if ( $phoneCel == NULL )
            {
                $phoneData = new stdClass();
                $phoneData->phone = $dados->celular;
                $phoneData->personId = $dados->personId;
                $phoneData->type = 'CEL';
                BasPhone::insertPhone($phoneData);
            }
            else
            {
                $phoneData = new stdClass();
                $phoneData->phone = $dados->celular;
                $phoneData->personId = $dados->personId;
                $phoneData->type = 'CEL';
                BasPhone::updatePhone($phoneData);
            }
        }
        
        return $ok;
    }
    
    public function trocarSenhaUsuario($idUser, $senha, $returnException = false)
    {
        $busUser = new BusinessAdminBusUser();
        return $busUser->trocarSenhaUsuario($idUser, $senha, $returnException);
    }
    
    public function listTiposAcessoExtenso( $tipoAcesso = NULL )
    {
        $acessos = array(
            self::USUARIO_ALUNO => _M('Aluno'),
            self::USUARIO_BASICO => _M('Usuário básico'),
            self::USUARIO_COORDENADOR => _M('Coordenador'),
            self::USUARIO_GESTOR => _M('Gestor'),
            self::USUARIO_PROFESSOR => _M('Professor'),
        );
        
        if ( strlen($tipoAcesso) > 0 )
        {
            $result = $acessos[$tipoAcesso];
        }
        else
        {
            $result = $acessos;
        }
        
        return $result;
    }

    public static function isAdmin()
    {
        if (array_key_exists( 'ADMIN', $_SESSION['login']->groups) && !(strlen(MIOLO::_REQUEST('isAdmin') > 0)) )
        {
            $isAdmin = DB_TRUE;
        }
        else
        {
            if ( strlen(MIOLO::_REQUEST('isAdmin') > 0 ) )
            {
                $isAdmin = MIOLO::_REQUEST('isAdmin');
            }
            else
            {
                $isAdmin = DB_FALSE;
            }
        }
        
        return $isAdmin;
    }
    
    /**
     * Verifica se a senha informada corresponde com a armazenada no sistema
     * 
     * @param type $senha
     */
    public static function verificaSenhaDoUsuario($senha)
    {
        $password = SAGU::getUsuarioLogado()->password;
        $mioloPassword = SAGU::getUsuarioLogado()->mioloPassword;
                
        return $password == md5($senha) || $password == $senha || $mioloPassword == md5($senha) || $mioloPassword == $senha;
    }
}
?>