<?php

/**
 * @author moises
 *
 * @since
 * Class created on 08/04/2013
 *
 */
$MIOLO->uses('tipos/capsolicitacao.class.php', 'contaspagar');

class capsolicitacaochefe extends capsolicitacao
{
    public function buscarNaReferencia($colunas, $valoresFiltrados = array( ))
    {
        $msql = parent::buscarNaReferencia($colunas, $valoresFiltrados);
        $personId = SAGU::getUsuarioLogado()->personId;
        
        //centro de custo com rateio não mostrava na grid
        $msql->setColumnsOverride(array('public.capsolicitacao.solicitacaoid','public.capsolicitacao.datasolicitacao','public.basperson.name','public.basphysicalperson.name','public.capsolicitacao.dadoscompra',
'CASE WHEN public.capsolicitacao.costcenterid IS NOT NULL THEN public.acccostcenter.description ELSE costCenterAut.description END','public.capsolicitacaoestado.nome'));
        
        // Filtra apenas por estados AGUARDANDO DEFERIMENTO
        $msql->addEqualCondition('public.capsolicitacao.solicitacaoestadoid', capsolicitacaoestado::AGUARDANDO_DEFERIMENTO);
        $msql->addLeftJoin('public.caprateio', 'public.caprateio.accountschemeid = public.capsolicitacao.accountschemeid');
        $msql->addLeftJoin('public.caprateioautorizacao','public.caprateioautorizacao.solicitacaoid = public.capsolicitacao.solicitacaoid');
        $msql->addLeftJoin('public.acccostcenter costCenterAut','public.caprateioautorizacao.costcenterid = costCenterAut.costcenterid');
        
        // Filtra apenas pelas solicitacoes cujo usuario logado é chefe do centro de custo respectivo
        //Correcao: não exibia solicitações com rateio
        $msql->setWhere('CASE WHEN public.capsolicitacao.costcenterid IS NOT NULL THEN '
                . 'EXISTS(SELECT 1 FROM accCostCenter WHERE personIdOwner = ? AND public.capsolicitacao.costcenterid = accCostCenter.costcenterId) ELSE '
                . 'public.caprateioautorizacao.personIdOwner = ? END', array($personId,$personId));
        
        return $msql;
    }
}

?>