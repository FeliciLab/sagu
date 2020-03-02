create or replace view res.encontrosofertadeunidadetematica as
    select a.ofertadeunidadetematicaid,
           e.personid as personidpreceptor_oferta,
           e.name as nomepreceptor_oferta,
           a.inicio as inicio_oferta,
           a.fim as fim_oferta,
           a.encerramento as dataencerramento_oferta,
           a.encerradopor as encerradopor_oferta,
           a.unidadetematicaid,
           b.descricao as descricao_unidadetematica,
           b.periodo as periodo_unidadetematica,
           b.sumula as sumula_unidadetematica,
           b.cargahoraria as cargahoraria_unidadetematica,
           b.frequenciaminima as frequenciaminima_unidadetematica,
           b.tipo as tipo_unidadetematica,
           (case when b.tipo = 'T' then 'TEÓRICA' else 'PRÁTICA'END) as descricaotipo_unidadetematica,
           f.encontroid as codigo_encontro,
           f.temaid as codigotema_encontro,
           g.descricao as descricaotema_encontro,
           f.inicio::date as datainicio_encontro,
           f.fim::date as datafim_encontro,
           f.inicio::time as horainicio_encontro,
           f.fim::time as horafim_encontro,
           f.cargahoraria as cargahoraria_encontro,
           f.conteudoministrado as conteudoministrado_encontro,
           f.ministrante as ministrante_encontro
      from res.ofertadeunidadetematica a
inner join res.unidadetematica b
        on a.unidadetematicaid = b.unidadetematicaid
inner join only basphysicalperson e
        on e.personid = a.personid
inner join res.encontro f
        on f.ofertadeunidadetematicaid = a.ofertadeunidadetematicaid
 left join res.tema g
        on f.temaid = g.temaid;
