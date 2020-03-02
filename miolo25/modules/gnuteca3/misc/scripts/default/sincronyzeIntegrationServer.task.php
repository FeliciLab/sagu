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
 * @author Lucas Gerhardt [lucas_gerhardt@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 28/04/2014
 *
 * */

class sincronyzeIntegrationServer extends GTask
{

    public $busFile;
    public $busLibraryUnit;
    public $busMaterial;


    /**
     * METODO CONSTRUCT É OBRIGATÓRIO, POIS A CLASSE DE SCHEDULE TASK SEMPRE VAI PASSAR O $MIOLO COMO PARAMETRO
     *
     * @param OBJECT $MIOLO
     */
    function __construct($MIOLO, $myTaskId)
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busFile = $this->MIOLO->getBusiness('gnuteca3' , 'BusFile');
        $this->busMaterial = $this->MIOLO->getBusiness('gnuteca3' , 'BusMaterial');
        
        parent::__construct($MIOLO, $myTaskId);
    }
    
    /**
     * MÉTODO OBRIGATORIO.
     * ESTE METODO SERA CHAMADO PELA CLASSE SCHEDULE TASK PARA EXECUTAR A TAREFA
     *
     * @return boolean
     */
    public function execute()
    {
        $this->busFile->folder = 'bases_novas';
        $this->busFile->filename = '';
        $this->busFile->extension = 'zip';
        $files = $this->busFile->searchFile();
        if ($files)
        {
            foreach ($files as $i => $file)
            {
                $fileName = str_replace($file[2], '', $file[0]);
                $ok = $this->busMaterial->sincronyzeMaterials($fileName, $file[3]);
                $destination = '/var/www/sagu/miolo25/modules/gnuteca3/html/files/bases_sincronizadas/' . $file[2];
                
                if($ok)
                {
                    unlink($fileName . 'gtcLibraryUnit.csv');
                    unlink($fileName . 'gtcMaterial.csv');
                    unlink($fileName . 'gtcMaterialControl.csv');
                    unlink($fileName . 'gtcExemplaryControl.csv');
                    
                    copy($file[0], $destination);
                    chmod($destination, 0777);
                    unlink($file[0]);
                }
            }
        }
        else
        {
            throw new Exception('Não existem arquivos para a sincronização!');
        }
        return true;
    }
}
?>
