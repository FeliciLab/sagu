CREATE VIEW cmn_grupo AS
    SELECT baslink.linkid AS codigodogrupo, baslink.description AS descricao, baslink.linkid AS nivel FROM baslink;

