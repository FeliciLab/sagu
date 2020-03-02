CREATE OR REPLACE VIEW unit_acdenrollconfig AS SELECT * FROM acdenrollconfig;
COMMENT ON VIEW unit_acdenrollconfig IS 'Criado view para manter compatibilidade com o sistema, sem a funcionalidade de multiunidade/multicentro.';

