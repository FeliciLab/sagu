create or replace view res.temasofertadeunidadetematica as
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
           d.temaid,
           c.cargahoraria as cargahoraria_temaoferta,
           d.descricao
      from res.ofertadeunidadetematica a
inner join res.unidadetematica b
        on a.unidadetematicaid = b.unidadetematicaid
inner join res.temadaunidadetematica c
        on c.ofertadeunidadetematicaid = a.ofertadeunidadetematicaid
inner join res.tema d
        on c.temaid = d.temaid
inner join only basphysicalperson e
        on e.personid = a.personid;
