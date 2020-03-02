<?php
/**
 *  Classe que adiciona os perfis da avaliação institucional ao MLogin instanciado na sessão no momento que o usuário tenta logar 
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/12/01
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2008 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

//$MIOLO->uses('classes/adatetime.class.php', 'base');
$MIOLO->uses('classes/ainternalservices.class.php', 'avinst');
class AManageLogin
{
    public function __construct($perfis)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getConf('login.sourceModule');
        $MIOLO->uses( "types/avaPerfil.class.php", $module );
        $login = $MIOLO->getLogin();
        $objPerfil = new avaPerfil();
        $perfisDb = $objPerfil->search();
        
        $perfisDb = AdminUnivates::getArrayOfTypes($perfisDb, 'avaPerfil', 'tipo');
        
        if (is_array($perfisDb))
        {
            $keyPerfis = array_keys($perfis);
            foreach ($perfisDb as $perfilDb)
            {
                if (in_array($perfilDb->tipo, $keyPerfis))
                {
                    $login->perfis[$perfilDb->idPerfil] = $perfis[$perfilDb->tipo];
                }
            }
        }
    }
    
    //
    // Coloca os profiles dentro da estrutura da Avaliação, buscando a chamada responsável
    // por buscar os perfis no sistema interno.
    //
    public static function getLoginProfiles($idPessoa)
    {
        $MIOLO = MIOLO::getInstance();
        if (strlen($idPessoa)>0)
        {
            $login = $MIOLO->getLogin();
            if (is_array($login->groups))
            {
                $MIOLO->uses('types/avaPerfil.class.php', 'avinst');
                $avaPerfil = new avaPerfil();
                $perfis = $avaPerfil->search(ADatabase::RETURN_TYPE);
                if (is_array($perfis))
                {
                    foreach ($perfis as $perfil)
                    {
                        if (in_array($perfil->tipo, $login->groups))
                        {
                            $perfisLogin[$perfil->idPerfil] = $perfil->tipo;
                        }
                    }
                }
            }
        }
        return $perfisLogin;
    }
}
?>  