CREATE TABLE acpCursoDocente (cursoDocenteId  SERIAL NOT NULL, cursoId int4 NOT NULL, docenteId int4 NOT NULL, ativo bool NOT NULL, CONSTRAINT acpCursoDocente_cursoDocenteId PRIMARY KEY (cursoDocenteId));
CREATE TABLE acpDocente (docenteId  SERIAL NOT NULL, nome int4, CONSTRAINT acpDocente_docenteId PRIMARY KEY (docenteId));
ALTER TABLE acpControleDeFrequencia ADD UNIQUE (controleDeFrequenciaId);
CREATE UNIQUE INDEX acpControleDeFrequencia_controleDeFrequenciaId ON acpControleDeFrequencia (controleDeFrequenciaId);
