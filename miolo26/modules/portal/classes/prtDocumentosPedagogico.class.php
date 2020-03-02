<?php

$MIOLO->uses('classes/breport.class', 'base');
$MIOLO->uses('types/AcpDocumentoPerfilCurso.class.php', 'pedagogico');
$MIOLO->uses('types/AcpTiposDocumentosPerfilCurso.class.php', 'pedagogico');

class prtDocumentosPedagogico
{
    public function getDocumentos($personid)
    {
        $type = new AcpInscricao();
        $sql = $type->msql();
        $sql->addEqualCondition('acpinscricao.personid', $personid);
        $sql->addNotEqualCondition('acpinscricao.situacao', AcpInscricao::SITUACAO_CANCELADO);
        foreach( $type->findMany($sql) as $cod=>$inscricao )
        {
            $ofertacurso = new AcpOfertaCurso($inscricao->ofertacursoid);
            
            $perfilcurso = $ofertacurso->ocorrenciacurso->curso->perfilcurso;
            
            //Lista todos documentos do perfil de curso a serem exibidos nas consultas diversas
            foreach( AcpDocumentoPerfilCurso::listarDocumentos($perfilcurso->perfilcursoid, NULL, NULL, true) as $cod => $documento )
            {
                $file = $documento->documento;
                $report = new BReport($file, 'pedagogico');
                $documentos[$file] = $report;
            }
        }
        
        return $documentos;
    }
}
?>
