CREATE OR REPLACE VIEW view_movimentacoes AS
 SELECT DISTINCT a.contractid AS contrato, COALESCE(d.periodid, e.periodid) AS periodo, b.statetime AS datahora, b.statecontractid AS codigo, c.description AS estado
   FROM acdcontract a
   JOIN acdmovementcontract b USING (contractid)
   JOIN acdstatecontract c USING (statecontractid)
   LEFT JOIN acdlearningperiod d USING (learningperiodid)
   LEFT JOIN acdlearningperiod e ON e.courseid::text = a.courseid::text AND e.courseversion = a.courseversion AND e.turnid = a.turnid AND e.unitid = a.unitid AND b.statetime::date >= e.begindate AND b.statetime::date <= e.enddate;

ALTER TABLE view_movimentacoes
  OWNER TO postgres;
