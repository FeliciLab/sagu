alter table acpcurso add situacao char(1);
alter table acpcurso add datainicio date;
alter table acpcurso add datafim date;

alter table acpcurso alter disciplinasadistancia set default 'f';
alter table acpgrauacademico alter nome type varchar;

alter table acpcurso rename cursoidrepresentanteid to cursorepresentanteid;