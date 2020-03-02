<?php

/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Form to manipulate generic reports
 *
 * @author Luís Felipe Wermann [luis_felipe@solis.com.br]
 *
 * @since
 * Class created on 10/04/2015
 *
 **/

class PrtCronogramaProfessor extends bTipo
{
    /**
     *
     * Chave primária da tabela.
     * 
     * @var int
     */
    private $cronogramaId;
    
    /**
     *
     * Código da disciplina oferecida.
     * 
     * @var int
     */
    private $groupId;
    
    /**
     *
     * Código do professor.
     * 
     * @var int
     */
    private $professorId;
    
    /**
     *
     * Cronograma previsto para a aula.
     * 
     * @var String
     */
    private $cronograma;
    
    /**
     *
     * Data da aula.
     * 
     * @var String
     */
    private $dataAula;


    /**
     * Construtor principal da classe.
     * 
     * @param int $groupId
     * @param int $professorId
     * @param String $cronograma
     * @param String $dataAula
     */
    public function __construct($groupId, $professorId, $cronograma, $dataAula)
    {
        $this->groupId = $groupId;
        $this->professorId = $professorId;
        $this->cronograma = $cronograma;
        $this->dataAula = $dataAula;

        //Chave primária
        $cronogramaId = $this->obterCodigoCronograma();
        if ( strlen($cronogramaId) > 0)
        {
            $this->cronogramaId = $cronogramaId;
        }
    }

    /**
     * 
     * Obtem código do cronograma (para o dia, disciplina e professor).
     * 
     * @return int
     */
    public function obterCodigoCronograma()
    {
        $msql = new MSQL('cronogramaid', 'prtcronogramaprofessor');
        $msql->addEqualCondition('groupId', $this->groupId);
        $msql->addEqualCondition('professorId', $this->professorId);
        $msql->addEqualCondition('dataAula', $this->dataAula);
        
        $return = bBaseDeDados::consultar($msql);
        
        return $return[0][0];
    }
    
    /**
     * Obtem o conteúdo do cronograma previsto cadastrado.
     * 
     * @return String
     */
    public function obterConteudoCronograma()
    {
        $msql = new MSQL('cronograma', 'prtcronogramaprofessor');
        $msql->addEqualCondition('cronogramaId', $this->cronogramaId);
         
        $return = bBaseDeDados::consultar($msql);

        return $return[0][0];
    }
    
    /**
     * Classifica registro como UPDATE ou INSERT e aplica a execução.
     * 
     * @return boolean
     */
    public function salvar()
    {
        $ok = false;
        if ( strlen($this->cronogramaId) > 0 )
        {
            $ok = $this->atualizar();
        }
        else
        {
            $ok = $this->inserir();
        }
        
        return $ok;
    }
    
    /**
     * 
     * Atualiza registro do cronograma na base de dados.
     * 
     * @return boolean
     */
    public function atualizar()
    {
        $sql = "UPDATE prtCronogramaProfessor
                   SET cronograma = ?
                 WHERE cronogramaId = ? " ;
        
        $sqlResolvido = SAGU::prepare($sql, array($this->cronograma, $this->cronogramaId), false);
        
        return bBaseDeDados::executar($sqlResolvido[0]);
    }
    
    
    /**
     * Inserir novo registro.
     * 
     * @return boolean
     */
    public function inserir()
    {
        $msql = new MSQL();
        
        $msql->setColumns('groupId, professorId, dataAula, cronograma');
        $msql->setTables('prtCronogramaProfessor');
        
        return bBaseDeDados::inserir($msql, array($this->groupId, $this->professorId, $this->dataAula, $this->cronograma));
    }
    
    
}
?>