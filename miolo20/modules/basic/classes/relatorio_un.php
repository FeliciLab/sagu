<?php
require 'sconsole.php';

$msql = new MSQL();
$msql->setDb(SDatabase::getInstance());

$sql = "SELECT courseid,
    courseversion, turnid, unitid
    FROM acdcourseoccurrence
    WHERE 1=1
    --AND (courseid,courseversion,turnid,unitid) = ('75', 2, 1, 1)
    ";
$msql->createFrom($sql);
//$msql->setLimit(10);

foreach ( SDatabase::queryAssociative($msql) as $row )
{
    extrairDados($row['courseid'], $row['courseversion'], $row['turnid'], $row['unitid']);
}

function extrairDados($courseId, $courseVersion, $turnId, $unitId)
{
    //$report = new SReport();
    //$report->setModule( 'basic' );
    //$report->setReportName( 'extracao_dados' );
    //$report->setFileType(SReport::FILETYPE_CSV);

    //$file = $report->findCurrentReport();

    //$report->generate();

    //header('Content-Description: File Transfer');
    //header('Content-Type: application/rss+xml');
    //header('Content-Disposition: attachment; filename='.basename($file));
    //header('Content-Length: ' . filesize($file));
    //
    //ob_clean();
    //flush();
    //readfile($file);
    //
    //exit;

    $sql = "SELECT C.personid,
        pp.name AS nome,
        D.content AS cpf,
        RG.content AS cart_ident,
        RG.organ AS cart_ident_exp,
        datetouser(PP.datebirth) AS data_nascimento,
        (CASE WHEN PP.sex = 'F' THEN 'FEMININO' ELSE 'MASCULINO' END) AS sexo,
        CNT.name AS nacionalidade,
        CI.name AS naturalidade,
        CI.stateid AS uf,
        MS.description AS estado_civil,
        COALESCE(MAE.name, PP.mothername) AS nome_mae,
        COALESCE(PAI.name, PP.fathername) AS nome_pai,
        U.description AS unidade_campus,
        COU.name AS curso,
        T.description as turno,
        CVT.description AS moda_curso,
        C.courseversion AS vers_curso,
        CC.curricularcomponentid AS cod_disc,
        CC.name AS disciplina,
        obternotaouconceitofinal(e.enrollid) AS nota_final_disc,
        CC.academicnumberhours AS ch_disc,
        LP.periodid AS periodo_disc,
        ES.description AS resultado_disc,
        E.frequency AS horas_presenca_disc,
        cu.semester AS semestre_disc,
        substr(lp.periodid,1,4) AS ano_disc,

        (select (CASE WHEN (lp1.periodid = lp.periodid) THEN 'Calouro' ELSE 'Veterano' END)
                from acdmovementcontract m 
          inner join acdlearningperiod lp1 using (learningperiodid)
               where m.contractid = e.contractid
                 and m.statecontractid = getparameter('BASIC', 'STATE_CONTRACT_ID_ENROLLED')::integer
            order by statetime asc
               limit 1) AS calouro_no_periodo_disc,

        SC.description AS situacao
        FROM acdenroll e
        inner join acdcontract c on c.contractid = e.contractid
        inner join acdcourseoccurrence coc on (coc.courseid,coc.courseversion,coc.turnid,coc.unitid) = (c.courseid,c.courseversion,c.turnid,c.unitid)
        inner join only basphysicalperson pp on pp.personid = c.personid
        --inner join rptcontrato co on c.personid = co.personid and c.contractid=co.contractid
        inner join acdgroup g on g.groupid = e.groupid
        inner join acdcurriculum cu on cu.curriculumid = g.curriculumid
        inner join acdcurricularcomponent cc on (cu.curricularcomponentid, cu.curricularcomponentversion) = (cc.curricularcomponentid, cc.curricularcomponentversion)
        inner join acdlearningperiod lp on lp.learningperiodid = g.learningperiodid
        inner join acdenrollstatus es on es.statusid = e.statusid
        inner join acdcourseversion CV on CV.courseid = C.courseid AND CV.courseversion = C.courseversion
        inner join acdcourseversiontype CVT ON CVT.courseversiontypeid = CV.courseversiontypeid
        left join acdstatecontract sc on sc.statecontractid = getcontractstate(C.contractid)

                LEFT JOIN basdocument D
                        ON d.personid = pp.personid
                    AND D.documenttypeid = GETPARAMETER('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::int
                LEFT JOIN basdocument RG
                        ON pp.personid = RG.personid
                    AND RG.documenttypeid = GETPARAMETER('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_RG')::int

                LEFT JOIN basPhysicalPersonKinship pkmae
                        on pkmae.personid = pp.personid
                    and pkmae.kinshipid = GETPARAMETER('BASIC', 'MOTHER_KINSHIP_ID')::int
                LEFT JOIN basPhysicalPersonKinship pkpai
                        on pkpai.personid = pp.personid
                    and pkpai.kinshipid = GETPARAMETER('BASIC', 'FATHER_KINSHIP_ID')::int
            LEFT JOIN ONLY basperson mae
                        ON mae.personId = pkmae.relativePersonId
            LEFT JOIN ONLY basperson pai
                        ON pai.personId = pkpai.relativePersonId
                LEFT JOIN basmaritalstatus ms
                        ON ms.maritalstatusid = pp.maritalstatusid
                LEFT JOIN basCountry CNT
                        ON pp.countryIdBirth = CNT.countryId

    left join acdcourse cou on cou.courseid=c.courseid

            INNER JOIN basTurn T
                    ON T.turnId = C.turnId
            INNER JOIN basUnit U
                    ON U.unitId = C.unitId
            LEFT JOIN basLocation L
                    ON L.locationId = U.locationId
            LEFT JOIN basCity CI
                    ON CI.cityId = L.cityId

        where (coc.courseid,coc.courseversion,coc.turnid,coc.unitid) = ('{$courseId}', {$courseVersion}, {$turnId}, {$unitId})
        AND CU.curriculumTypeId = GETPARAMETER('ACADEMIC', 'ACD_CURRICULUM_TYPE_MINIMUM')::int
        AND (LP.begindate, LP.enddate) overlaps ('2010-01-01'::date, '2013-06-30'::date)
        order by pp.name, cc.curricularcomponentid";

    $msql = new MSQL();
    $msql->setDb(SDatabase::getInstance());
    $msql->createFrom($sql);
//    $msql->setLimit(2000);
    $query = SDatabase::queryAssociative( $msql );

    if ( count($query) <= 0 )
    {
        echo "Nao ha dados para {$courseId} - {$courseVersion} - {$turnId} - {$unitId}, pulando...\n";
        return;
    }
    
    $dados = array();
    $conta = array();
    $contaDisc = 0;
    $disciplinas = array();

    //foreach ( $query as $key => $row )
    //{
    //    if ( !isset($disciplinas[ $row['cod_disc'] ]) )
    //    {
    //        $disciplinas[ $row['cod_disc'] ] = $row['cod_disc'];
    //        $contaDisc ++;
    //    }
    //}

    foreach ( $query as $key => $row )
    {
        if ( !isset($disciplinas[ $row['cod_disc'] ] ))
        {
            $disciplinas[ $row['cod_disc'] ] = ++ $contaDisc;
        }
    //    $disciplinas[ $row['cod_disc'] ] = $row['cod_disc'];
    }

    foreach ( $query as $key => $row )
    {
    //    flog($row['cod_disc']);

        $linha = (array) $dados[ $row['personid'] ];

        if ( !$conta[ $row['personid'] ] )
        {
            $conta[ $row['personid'] ] = 0;
        }

        $conta[ $row['personid'] ] ++;

        $dcols = array('disciplina', 'nota_final_disc', 'ch_disc', 'periodo_disc', 'resultado_disc', 'horas_presenca_disc', 'ano_disc', 'semestre_disc', 'calouro_no_periodo_disc');

        foreach ( $row as $col => $value )
        {
            if ( in_array($col, array('personid', 'cod_disc', 'situacao')) || in_array($col, $dcols) )
            {
                continue;
            }

            $linha[$col] = $value;
        }

        // aloca linhas corretamente
        foreach ( $disciplinas as $cod_disc )
        {
            foreach ( $dcols as $col )
            {
                if ( !isset($linha[$col . '_' . $cod_disc]) )
                {
                    $linha[$col . '_' . $cod_disc] = null;
                }
            }
        }

        foreach ( $row as $col => $value )
        {
            if  ( in_array($col, $dcols) )
            {
                $linha[$col . '_' . $disciplinas[$row['cod_disc']]] = $value;
            }
        }

        $dados[ $row['personid'] ] = $linha;
    }

    foreach ( $query as $row )
    {
        if ( !isset($dados[$row['personid']]['situacao']))
        {
            $dados[$row['personid']]['situacao'] = $row['situacao'];
        }
    }

    $lines = array();
    $lines[] = arrayToCsv( array_keys( current($dados) ) );

    foreach ( $dados as $row )
    {
        $lines[] = arrayToCsv($row);
    }

    $csv = implode("\n", $lines);
    $file = dirname(__FILE__) . '/unidade_' . $unitId . '_curso_' . $courseId . '_versao_' . $courseVersion . '_turno' . $turnId . '.csv';
    
    file_put_contents($file, $csv);
    
    echo "Gerado: {$file}\n";
}



function arrayToCsv( array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false ) {
    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');

    $output = array();
    foreach ( $fields as $field ) {
        if ($field === null && $nullToMysqlNull) {
            $output[] = 'NULL';
            continue;
        }

        // Enclose fields containing $delimiter, $enclosure or whitespace
        if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
            $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
        }
        else {
            $output[] = $field;
        }
    }

    return implode( $delimiter, $output );
}
?>
