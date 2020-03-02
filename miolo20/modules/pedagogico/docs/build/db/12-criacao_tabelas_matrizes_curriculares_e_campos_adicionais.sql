CREATE TABLE acpMatrizCurricular (matrizCurricularId  SERIAL NOT NULL, descricao varchar(255), CONSTRAINT acpMatrizCurricular_matrizCurricularId PRIMARY KEY (matrizCurricularId));
CREATE TABLE acpMatrizCurricularCurso (matrizCurricularCursoId  SERIAL NOT NULL, cursoId int4, matrizCurricularId int4, series int4, situacao char(1) NOT NULL, dataInicial date NOT NULL, dataFinal date, CONSTRAINT acpMatrizCurricularCurso_matrizCurricularCursoId PRIMARY KEY (matrizCurricularCursoId));
CREATE TABLE acpOcorrenciaCurso (ocorrenciaCursoId  SERIAL NOT NULL, situacao char(1) NOT NULL, dataExtincao date, dataInativo date, cursoId int4, turnId int4, CONSTRAINT acpOcorrenciaCurso_ocorrenciaCursoId PRIMARY KEY (ocorrenciaCursoId));
CREATE TABLE acpCamposAdicionaisCurso (camposAdicionaisCursoId  SERIAL NOT NULL, perfilCursoCamposAdicionaisId int4, cursoId int4, dados int4, CONSTRAINT acpCamposAdicionaisCurso_camposAdicionaisCursoId PRIMARY KEY (camposAdicionaisCursoId));
ALTER TABLE acpControleDeFrequencia ADD UNIQUE (controleDeFrequenciaId);
ALTER TABLE acpMatrizCurricularCurso ADD CONSTRAINT acpMatrizCurricular_matrizCurricularCursoId_pk FOREIGN KEY (matrizCurricularId) REFERENCES acpMatrizCurricular (matrizCurricularId);
ALTER TABLE acpOcorrenciaCurso ADD CONSTRAINT basTurn_ocorrenciaCursoId_pk FOREIGN KEY (turnId) REFERENCES basTurn (turnId);
ALTER TABLE acpMatrizCurricularCurso ADD CONSTRAINT acpMatrizCurricularCurso_cursoId_pk FOREIGN KEY (cursoId) REFERENCES acpCurso (cursoId);
ALTER TABLE acpOcorrenciaCurso ADD CONSTRAINT acpCurso_ocorrenciaCursoId_pk FOREIGN KEY (cursoId) REFERENCES acpCurso (cursoId);
ALTER TABLE acpCamposAdicionaisCurso ADD CONSTRAINT acpCamposAdicionaisCurso_perfilCursoCamposAdicionaisId_pk FOREIGN KEY (perfilCursoCamposAdicionaisId) REFERENCES acpPerfilCursoCamposAdicionais (perfilCursoCamposAdicionaisId);
ALTER TABLE acpCamposAdicionaisCurso ADD CONSTRAINT acpCamposAdicionaisCurso_cursoId_pk FOREIGN KEY (cursoId) REFERENCES acpCurso (cursoId);

