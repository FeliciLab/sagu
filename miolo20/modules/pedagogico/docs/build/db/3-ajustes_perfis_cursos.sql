ALTER TABLE acpPerfilCurso DROP situacao;
ALTER TABLE acpPerfilCurso ADD ativo BOOLEAN NOT NULL DEFAULT TRUE;
ALTER TABLE acpPerfilCurso ADD descricao VARCHAR(255);
ALTER TABLE acpTipoComponenteCurricular ALTER descricao TYPE VARCHAR(255);
