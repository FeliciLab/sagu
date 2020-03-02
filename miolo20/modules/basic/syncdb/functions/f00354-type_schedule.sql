DROP TYPE IF EXISTS type_schedule CASCADE;

CREATE TYPE type_schedule AS (
    professorid integer, 
    professor varchar, 
    groupid integer, 
    disciplina varchar, 
    groupid_choque integer, 
    disciplina_choque varchar,
    unidade varchar,
    datas text,
    horarios text,
    diassemana text);