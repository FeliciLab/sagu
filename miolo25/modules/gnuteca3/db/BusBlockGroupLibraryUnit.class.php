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
 * Classe para bloquear unidade de biblioteca por grupo de usuário.
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br] 
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 11/07/2012
 *
 **/
class BusinessGnuteca3BusBlockGroupLibraryUnit extends GBusiness
{
    /**
     * @var BusPersonLibraryUnit Business da unidade de biblioteca.
     */
    private $busPersonLibraryUnit;
    
    /**
     * @var BusBond Business de grupo.
     */
    private $busBond;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->busPersonLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusPersonLibraryUnit');
        $this->busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');
    }

    /**
     * Checa se usuário pode realizar empréstimo em determinada unidade de biblioteca.
     * 
     * @param int $personId Código da pessoa.
     * @param int $libraryUnitId Código da unidade de biblioteca.
     * @return boolean Retorna positivo caso tiver permissão.
     */
    public function checkAccess($personId, $libraryUnitId)
    {
        // Obtém grupo ativo e de maior nível do usuário (mesmo nível usado para obter as políticas).
        $personLink = $this->busBond->getActivePersonLink($personId);
        
        if ( is_object($personLink) )
        {
            // Verifica se o grupo ativo do usuário possui permissão de empréstimo em determinada unidade de biblioteca passada por parâmetro.
            return $this->checkIfGroupCanBorrowForUnity($personLink->activelink, $libraryUnitId);
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
     * Verifica se grupo possui permissão de empréstimo em determinada unidade de biblioteca.
     * 
     * @param int $groupId Código do grupo de usuário.
     * @param int $libraryUnitId Código da unidade de biblioteca.
     * @return boolean Positivo se não tiver restrição do grupo em determinada unidade de biblioteca.
     */
    private function checkIfGroupCanBorrowForUnity($groupId, $libraryUnitId)
    {
        $lines = explode("\n", BLOCK_GROUP_X_UNIT);
        
        foreach ( $lines as $line )
        {
            $valor = explode("=", str_replace(' ', '', $line));
            
            // Obtém o código do grupo.
            $groupIdParameter = $valor[0];
            
            // Obtém código da unidade de biblioteca.
            $libraryUnitIdParameter = $valor[1];
            
            if ( ($groupId == $groupIdParameter) && ($libraryUnitIdParameter == $libraryUnitId) )
            {
                return FALSE;
            }
        }
        
        return TRUE;
    }
    
}
?>
