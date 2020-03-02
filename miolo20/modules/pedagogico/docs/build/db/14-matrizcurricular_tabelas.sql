ALTER TABLE acpMatrizCurricular ADD COLUMN matrizCurricularCursoId int4;
ALTER TABLE acpMatrizCurricular ADD COLUMN ofereceHabilitacao bool NOT NULL;
ALTER TABLE acpMatrizCurricular ADD COLUMN descricaoHabilitacao int4;
ALTER TABLE acpMatrizCurricular ADD COLUMN ordem int4 NOT NULL;
ALTER TABLE acpMatrizCurricularCurso ADD COLUMN descricao varchar(255);
CREATE TABLE acpMatrizCurricularSerHab (matrizCurricSerHabId  SERIAL NOT NULL, matrizCurricularId int4, habilidade int4, descritivo int4, CONSTRAINT acpMatrizCurricularSerHab_matrizCurricSerHabId PRIMARY KEY (matrizCurricSerHabId));
ALTER TABLE acpMatrizCurricular ADD CONSTRAINT acpMatrizCurricular_matrizCurricularCursoId_pk FOREIGN KEY (matrizCurricularCursoId) REFERENCES acpMatrizCurricularCurso (matrizCurricularCursoId);
ALTER TABLE acpMatrizCurricularSerHab ADD CONSTRAINT acpMatrizCurricular_matrizCurricSerHabId_pk FOREIGN KEY (matrizCurricularId) REFERENCES acpMatrizCurricular (matrizCurricularId);

