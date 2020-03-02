ALTER TABLE acpOcorrenciaCurso ADD COLUMN unitId int4;
ALTER TABLE acpOcorrenciaCurso ADD CONSTRAINT acpOcorrenciaCurso_unitId_pk FOREIGN KEY (unitId) REFERENCES basUnit (unitId);

