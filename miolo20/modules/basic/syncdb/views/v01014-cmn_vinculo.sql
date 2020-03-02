CREATE VIEW cmn_vinculo AS
    SELECT baspersonlink.personid AS codigodapessoa, baspersonlink.linkid AS codigodogrupo, baspersonlink.datevalidate AS datavalidade FROM baspersonlink;
