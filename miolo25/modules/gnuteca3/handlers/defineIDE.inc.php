<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 *
 * Este arquivo é parte do programa Gnuteca.
 *
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 21/07/2011
 *
 * Utilizado para autocomplete em IDE's (Netbeans ,Zend e Eclipse)
 *
 **/
#SELECT  '/**\n * ' || description || '\n */' || '\n define(''' || parameter || ''',' || '''' ||  replace( substring(value,0,100),'''', '\\''') ||''');\n' FROM basConfig;
if ( false )
{
    /**
     * Tipo de controle de material. 1 - Gênero de material para obra. 2 - Gênero do material por exemplar
     */
     define('MATERIAL_GENDER_CONTROL','2');

    /**
     * Tipo de controle de material. 1 - Tipo de material para obra. 2 - Tipo do material por exemplar
     */
     define('MATERIAL_TYPE_CONTROL','2');

    /**
     * Definição do campo Marc para o título.
     */
     define('MARC_TITLE_TAG','245.a');

    /**
     * Quando a configuração MATERIAL_TYPE_CONTROL for igual a 2 esta força o controle do tipo de material por obra.
     */
     define('MATERIAL_TYPE_FORCE_BY_MATERIAL','BA
    SA
    SE,#');

    /**
     * Quando a configuração MATERIAL_TYPE_CONTROL for igual a 2 esta força o controle do tipo de material por exemplar.
     */
     define('MATERIAL_TYPE_FORCE_BY_EXEMPLARY','BK
    CF
    MU
    MX
    VM
    AM
    MP
    SE,12345678uz');

    /**
     * Definição do campo Marc para o autor.
     */
     define('MARC_AUTHOR_TAG','100.a');

    /**
     * Relaciona a lista de campos marc que devem aparecer na listagem de exemplares dos detalhes da pesquisa. Para o operador.
     */
     define('SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_OPERATOR','Volume=949.v, Tomo=949.t, Unidade de biblioteca=949.b, Tipo de material=949.1, Tipo físico do mater');

    /**
     * Informa a tag de assunto.
     */
     define('MARC_SUBJECT_TAG','650.a');

    /**
     * Define se é enviado comprovante de renovação web.
     */
     define('USER_SEND_RECEIPT_RENEW_WEB','t');

    /**
     * Define na abertura do Gnuteca a exibição ou não das dicas do sistema.
     */
     define('SHOW_TIPS','YES');

    /**
     * Utiliza a data de empréstimo definida nas políticas para a renovação. Caso não utilize o sistema irá utilizar os dias de empréstimo para a renovação.
     */
     define('USE_LOAN_DATE_FOR_RENEW','t');

    /**
     * Seta o tempo maximo de execução na importação de arquivos ISO2709
     */
     define('ISO2709_MAX_EXECUTION_TIME','15000');

    /**
     * Seta o maximo de memoria para consumo do php.
     */
     define('ISO2709_MEMORY_LIMIT','256M');

    /**
     * Seta o tamanho maximo do POST.
     */
     define('ISO2709_MAX_POST_SIZE','16M');

    /**
     * Em uma renovação, quantos dias antes da data prevista, pode incrementar a data prevista de devolução;
     */
     define('DAYS_BEFORE_DATE_OF_RETURN_CAN_INCREASE','2');

    /**
     * Relaciona campos da catalogação com business. Não altere o conteúdo desta constante!
     */
     define('RELATIONSHIP_OF_FIELDS_WITH_TABLES_FOR_SELECTS','MARC_EXEMPLARY_ORIGINAL_LIBRARY_UNIT_ID_TAG,MARC_KARDEX_LIBRARY_UNIT_ID_TAG,MARC_EXEMPLARY_LIBRARY_');

    /**
     * Tags a serem ignoradas na exportação de ISO 2709.
    Ex:9,000,040.a
    Neste exemplo, serão ignoradas todas as tags que começam com 9, como (901.a, 901.b, ...), também serão ignoradas as tags 000 e 040.a.
    Os valores devem ser separadas por ",".
     */
     define('ISO2709_EXPORT','9');

    /**
     * Tags a serem ignoradas na importação de ISO 2709.
    Ex:9,000,040.a Neste exemplo, serão ignoradas todas as tags que começam com 9, como (901.a, 901.b, ...), também serão ignoradas as tags 000 e 040.a.
     */
     define('ISO2709_IMPORT','001');

    /**
     * Define se a opção de enviar recibo de devolucao sai marcada.
     */
     define('MARK_SEND_RETURN_MAIL_RECEIPT','f');

    /**
     * Define o endereço de e-mail da biblioteca. Esse endereço é utilizado para envio dos e-mails de emprestimo entre bibliotecas
     */
     define('EMAIL_ADMIN_RESERVE','admbiblio@univates.br');

    /**
     * 1 - autenticação via código da pessoa
    2 - autenticação via campo login
    3 - autenticação campo login/base
     */
     define('MY_LIBRARY_AUTHENTICATE_TYPE','1');

    /**
     * Define o titulo de email que envia um comunicado de reserva cancelada para o usuário.
     */
     define('EMAIL_RESERVE_ANSWERED_ADMIN_RESULT_SUBJECT','Comunicação de Reservas');

    /**
     * Suprime as mensagens "Não há empréstimos em aberto" e "Reserva do exemplar X atendida para o usuário Y" na finalização da circulação de material.
     */
     define('SUPRESS_RETURN_MESSAGE','t');

    /**
     * Conteudo do e-mail que é enviado para o administrado com o resultado do comunicado de reservas atendidas.
     */
     define('EMAIL_RESERVE_ANSWERED_ADMIN_RESULT_CONTENT','Segue abaixo o resultado do comunicado de reservas atendidas.$LN $CONTENT');

    /**
     * Define o sufixo no assunto do e-mail que será enviado ao usuário anexando o recibo de alteração de multa. Complementa o parâmetro EMAIL_FINE_RECEIPT_CONTENT.
     */
     define('EMAIL_FINE_RECEIPT_SUBJECT','Recibo de Alteração de Multa');

    /**
     * Preferência que ativa/desativa o campo código no cadastro de pessoa. Quando o valor é true, o campo aparece no cadastro, caso contrário, o código é atribuído automáticamente.
     */
     define('USER_ESPECIFICAR_CODIGO_MANUALMENTE','f');

    /**
     * Vincula a tela de operadores com as pessoas cadastradas no gnuteca.
     */
     define('PERSON_IS_A_OPERATOR','t');

    /**
     * Substitui valores na importação de Marc 21.
    Modo de usar: valor original=valor novo
    Novos valores devem ser separados por quebra de linha.
    Exemplo:
        =
    LDR=000
    LEADER=000
    LÍDER=000
     */
     define('MARC21_REPLACE_VALUES','	=
    LDR=000
    LEADER=000
    LÍDER=000');

    /**
     * Arredonda dias de penalidade por atraso para cima caso (true) ou para baixo caso (false).
     */
     define('ROUND_PENALTY_BY_DELAY','t');

    /**
     * Etiqueta de editora
     */
     define('MARC_EDITOR_TAG','260.b');

    /**
     * Define o SearchFormatId para o intercâmbio.
     */
     define('INTERCHANGE_SEARCH_FORMAT_ID','7');

    /**
     * Define o Tamanho celula de descrição de uma table raw
     */
     define('TABLE_RAW_DESCRIPTION_CELL_SIZE','150');

    /**
     * Caracter que o banco retorna dos campos do tipo boolean, que vem com o valor true
     */
     define('DB_TRUE','t');

    /**
     * Caracter que o banco retorna dos campos do tipo boolean, que vem com o valor false
     */
     define('DB_FALSE','f');

    /**
     * Nome da base de dados a ser utilizada pelo gnuteca3.
     */
     define('DB_NAME','gnuteca3');

    /**
     * Define o código para o estado de reserva solicitada
     */
     define('ID_RESERVESTATUS_REQUESTED','1');

    /**
     * Define o código para o estado de reserva atendida
     */
     define('ID_RESERVESTATUS_ANSWERED','2');

    /**
     * Define o código para o estado de reserva comunicada
     */
     define('ID_RESERVESTATUS_REPORTED','3');

    /**
     * Define o código para o estado de reserva confirmada
     */
     define('ID_RESERVESTATUS_CONFIRMED','4');

    /**
     * Define o código para o estado de reserva vencida
     */
     define('ID_RESERVESTATUS_UNSUCCESSFUL','5');

    /**
     * Define o código para o estado de reserva cancelada
     */
     define('ID_RESERVESTATUS_CANCELLED','6');

    /**
     * Define o código para o tipo de empréstimo padrão
     */
     define('ID_LOANTYPE_DEFAULT','1');

    /**
     * Define o código para o tipo de empréstimo forçado
     */
     define('ID_LOANTYPE_FORCED','2');

    /**
     * Define o código para o tipo de empréstimo momentâneo
     */
     define('ID_LOANTYPE_MOMENTARY','3');

    /**
     * Define o código para a operação de empréstimo
     */
     define('ID_OPERATION_LOAN','1');

    /**
     * Define o código para a operação de empréstimo com penalidade
     */
     define('ID_OPERATION_LOAN_DELAY_LOAN','20');

    /**
     * Define o código para a operação de empréstimo com penalidade
     */
     define('ID_OPERATION_LOAN_PENALTY','21');

    /**
     * Define o código para a operação de empréstimo com multa
     */
     define('ID_OPERATION_LOAN_FINE','22');

    /**
     * Define o código para a operação de devolução
     */
     define('ID_OPERATION_RETURN','2');

    /**
     * Define o código para a operação de empréstimo entre unidades
     */
     define('ID_OPERATION_LOAN_BETWEEN_UNITS','3');

    /**
     * Define o código para a operação de empréstimo entre unidades - Confirmação de recebimento
     */
     define('ID_OPERATION_LOAN_BETWEEN_UNITS_CONFIRM_RECEIPT','5');

    /**
     * Define o código para a operação de devolução entre unidades
     */
     define('ID_OPERATION_RETURN_BETWEEN_UNITS','4');

    /**
     * Define o código para a operação de atender reserva
     */
     define('ID_OPERATION_MEET_RESERVE','14');

    /**
     * Define o código para a operação de reserva local
     */
     define('ID_OPERATION_LOCAL_RESERVE','10');

    /**
     * Define o código para a operação de cancelamento de reserva local
     */
     define('ID_OPERATION_CANCEL_RESERVE','15');

    /**
     * Define o código para a operação de reserva local no estado inicial
     */
     define('ID_OPERATION_LOCAL_RESERVE_IN_INITIAL_STATUS','11');

    /**
     * Define o código para a operação de reserva web
     */
     define('ID_OPERATION_WEB_RESERVE','12');

    /**
     * Define o código para a operação de reserva web no estado inicial
     */
     define('ID_OPERATION_WEB_RESERVE_IN_INITIAL_STATUS','13');

    /**
     * Codigo do estado da multa em aberto
     */
     define('ID_FINESTATUS_OPEN','1');

    /**
     * Código de Renovação Local.
     */
     define('ID_RENEWTYPE_LOCAL','1');

    /**
     * Código de Renovação Web.
     */
     define('ID_RENEWTYPE_WEB','2');

    /**
     * Código de estado inicial do exemplar
     */
     define('ID_EXEMPLARYSTATUS_INITIAL','1');

    /**
     * Código de estado anterior do exemplar
     */
     define('ID_EXEMPLARYSTATUS_PREVIOUS','2');

    /**
     * Código do estado de emprestimo entre unidades - SOLICITADO
     */
     define('ID_LOANBETWEENLIBRARYSTATUS_REQUESTED','1');

    /**
     * Código do estado de emprestimo entre unidades - CANCELADO
     */
     define('ID_LOANBETWEENLIBRARYSTATUS_CANCELED','2');

    /**
     * Código do estado de emprestimo entre unidades - APPROVED
     */
     define('ID_LOANBETWEENLIBRARYSTATUS_APPROVED','3');

    /**
     * Código do estado de emprestimo entre unidades - REPROVADO
     */
     define('ID_LOANBETWEENLIBRARYSTATUS_DISAPPROVED','4');

    /**
     * Código do estado de emprestimo entre unidades - CONFIRMADO
     */
     define('ID_LOANBETWEENLIBRARYSTATUS_CONFIRMED','5');

    /**
     * Código do estado de emprestimo entre unidades - DEVOLUCAO
     */
     define('ID_LOANBETWEENLIBRARYSTATUS_DEVOLUTION','6');

    /**
     * Código do estado de emprestimo entre unidades - FINALIZADO
     */
     define('ID_LOANBETWEENLIBRARYSTATUS_FINALIZED','7');

    /**
     * Definição do campo Marc para o número de controle
     */
     define('MARC_CONTROL_NUMBER_TAG','001.a');

    /**
     * Definição do campo Marc para os campos fixos
     */
     define('MARC_FIXED_DATA_TAG','008.a');

    /**
     * Definição do campo Marc para os campos fixos
     */
     define('MARC_FIXED_DATA_FIELD','008');

    /**
     * Definição do campo Marc para o leader
     */
     define('MARC_LEADER_TAG','000.a');

    /**
     * Definição do campo Marc para o tipo do material
     */
     define('MARC_MATERIAL_TYPE_TAG','901.a');

    /**
     * Definição do campo Marc para o tipo do material
     */
     define('MARC_MATERIAL_PHYSICAL_TYPE_TAG','901.c');

    /**
     * Definição do campo Marc para o gênero do material
     */
     define('MARC_MATERIAL_GENDER_TAG','902.a');

    /**
     * Definição do campo Marc para a classificação.
     */
     define('MARC_CLASSIFICATION_TAG','090.a,080.a');

    /**
     * Definição do campo Marc para o cutter.
     */
     define('MARC_CUTTER_TAG','090.b');

    /**
     * Definição do campo Marc para o número da obra.
     */
     define('MARC_WORK_NUMBER_TAG','950.a');

    /**
     * Definicao do MARC para caracteres vazios
     */
     define('MARC_SPACE','#');

    /**
     * Definicao do campo MARC marc para fornecedor
     */
     define('MARC_SUPPLIER_TAG','947.a');

    /**
     * Definicao do campo MARC marc para fornecedor
     */
     define('MARC_ANALITIC_ENTRACE_TAG','773.w');

    /**
     * Definicao do campo MARC marc para informações de volume, etc
     */
     define('MARC_PERIODIC_INFORMATIONS','362.a');

    /**
     * Definicao do campo MARC marc para informações de volume, etc
     */
     define('KARDEX_PERIOD','310.a');

    /**
     * Definição do campo Marc para os exemplares
     */
     define('MARC_EXEMPLARY_FIELD','949');

    /**
     * Definição do campo Marc para numero de tombo dos exemplares
     */
     define('MARC_EXEMPLARY_ITEM_NUMBER_TAG','949.a');

    /**
     * Definição do campo Marc para a unidade dos exemplares
     */
     define('MARC_EXEMPLARY_LIBRARY_UNIT_ID_TAG','949.b');

    /**
     * Definição do campo Marc para a unidade original dos exemplares
     */
     define('MARC_EXEMPLARY_ORIGINAL_LIBRARY_UNIT_ID_TAG','949.9');

    /**
     * Definição do campo Marc para o tipo de aquisição dos exemplares
     */
     define('MARC_EXEMPLARY_ACQUISITION_TYPE_TAG','949.c');

    /**
     * Definição do campo Marc para o gênero do Material dos exemplares
     */
     define('MARC_EXEMPLARY_MATERIAL_GENDER_TAG','949.d');

    /**
     * Definição do campo Marc para o gênero do Material dos exemplares
     */
     define('MARC_EXEMPLARY_MATERIAL_TYPE_TAG','949.1');

    /**
     * Definição do campo Marc para o gênero do Material dos exemplares
     */
     define('MARC_EXEMPLARY_MATERIAL_PHYSICAL_TYPE_TAG','949.3');

    /**
     * Definição do campo Marc para o exemplar dos exemplares
     */
     define('MARC_EXEMPLARY_EXEMPLARY_TAG','949.e');

    /**
     * Definição do campo Marc para o estado dos exemplares
     */
     define('MARC_EXEMPLARY_EXEMPLARY_STATUS_TAG','949.g');

    /**
     * Definição do campo Marc para o estado dos exemplares
     */
     define('MARC_EXEMPLARY_EXEMPLARY_STATUS_FUTURE_TAG','949.i');

    /**
     * Definição do campo Marc para o patrimonio dos exemplares
     */
     define('MARC_EXEMPLARY_PATRIMONY_TAG','949.n');

    /**
     * Definição do campo Marc para o volume dos exemplares
     */
     define('MARC_EXEMPLARY_VOLUME_TAG','949.v');

    /**
     * Definição do campo Marc para a observação dos exemplares
     */
     define('MARC_EXEMPLARY_OBSERVATION_TAG','949.w');

    /**
     * Definição do campo Marc para o número dos exemplares
     */
     define('MARC_EXEMPLARY_EXEMPLARY_ID_TAG','949.x');

    /**
     * Definição do campo Marc para a data de entrada dos exemplares
     */
     define('MARC_EXEMPLARY_ENTRACE_DATE_TAG','949.y');

    /**
     * Definição do campo Marc para a data de baixa dos exemplares
     */
     define('MARC_EXEMPLARY_LOW_DATE_TAG','949.z');

    /**
     * Definição do campo Marc para a tomo dos exemplares
     */
     define('MARC_EXEMPLARY_TOMO_TAG','949.t');

    /**
     * Definição do campo Marc para o centro de custo dos exemplares
     */
     define('MARC_EXEMPLARY_COST_CENTER_TAG','949.q');

    /**
     * Definição do subcampo Marc para numero de tombo dos exemplares
     */
     define('MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD','a');

    /**
     * Definição do subcampo Marc para unidade dos exemplares
     */
     define('MARC_EXEMPLARY_LIBRARY_UNIT_ID_SUBFIELD','b');

    /**
     * Definição do subcampo Marc para unidade original dos exemplares
     */
     define('MARC_EXEMPLARY_ORIGINAL_LIBRARY_UNIT_ID_SUBFIELD','9');

    /**
     * Definição do subcampo Marc para tipo de aquisição dos exemplares
     */
     define('MARC_EXEMPLARY_ACQUISITION_TYPE_SUBFIELD','c');

    /**
     * Definição do subcampo Marc para gênero do material dos exemplares
     */
     define('MARC_EXEMPLARY_MATERIAL_GENDER_SUBFIELD','d');

    /**
     * Definição do subcampo Marc para gênero do material dos exemplares
     */
     define('MARC_EXEMPLARY_MATERIAL_TYPE_SUBFIELD','1');

    /**
     * Definição do subcampo Marc para gênero do material dos exemplares
     */
     define('MARC_EXEMPLARY_MATERIAL_PHYSICAL_TYPE_SUBFIELD','3');

    /**
     * Definição do subcampo Marc para o exemplar dos exemplares
     */
     define('MARC_EXEMPLARY_EXEMPLARY_SUBFIELD','e');

    /**
     * Definição do subcampo Marc para o status dos exemplares
     */
     define('MARC_EXEMPLARY_EXEMPLARY_STATUS_SUBFIELD','g');

    /**
     * Definição do subcampo Marc para o status futuro dos exemplares
     */
     define('MARC_EXEMPLARY_EXEMPLARY_STATUS_FUTURE_SUBFIELD','i');

    /**
     * Definição do subcampo Marc para o patrimonio dos exemplares
     */
     define('MARC_EXEMPLARY_PATRIMONY_SUBFIELD','n');

    /**
     * Definição do subcampo Marc para o volume dos exemplares
     */
     define('MARC_EXEMPLARY_VOLUME_SUBFIELD','v');

    /**
     * Definição do subcampo Marc para a observação dos exemplares
     */
     define('MARC_EXEMPLARY_OBSERVATION_SUBFIELD','w');

    /**
     * Definição do subcampo Marc para o número dos exemplares
     */
     define('MARC_EXEMPLARY_EXEMPLARY_ID_SUBFIELD','x');

    /**
     * Definição do subcampo Marc para a data de entrada dos exemplares
     */
     define('MARC_EXEMPLARY_ENTRACE_DATE_SUBFIELD','y');

    /**
     * Definição do subcampo Marc para a data de baixa dos exemplares
     */
     define('MARC_EXEMPLARY_LOW_DATE_SUBFIELD','z');

    /**
     * Definição do subcampo Marc para a tomo dos exemplares
     */
     define('MARC_EXEMPLARY_TOMO_SUBFIELD','t');

    /**
     * Definição do campo Marc para o kardex
     */
     define('MARC_KARDEX_FIELD','960');

    /**
     * Definição do campo Marc para codigo do assinante
     */
     define('MARC_KARDEX_SUBSCRIBER_ID_TAG','960.a');

    /**
     * Definição do campo Marc para codigo da unidade
     */
     define('MARC_KARDEX_LIBRARY_UNIT_ID_TAG','960.b');

    /**
     * Definição do campo Marc para tipo de aquisição
     */
     define('MARC_KARDEX_ACQUISITION_TYPE_TAG','960.c');

    /**
     * Definição do campo Marc para vencimento da assinatura
     */
     define('MARC_KARDEX_SIGNATURE_END_TAG','960.d');

    /**
     * Definição do campo Marc para data da assinatura
     */
     define('MARC_KARDEX_SIGNATURE_DATE_TAG','960.h');

    /**
     * Definição do campo Marc para data da entrada
     */
     define('MARC_KARDEX_ENTRACE_DATE_TAG','960.y');

    /**
     * Definição do campo Marc para publicação
     */
     define('MARC_KARDEX_PUBLICATION_TAG','960.j');

    /**
     * Definição do subcampo Marc para codigo do assinante
     */
     define('MARC_KARDEX_SUBSCRIBER_ID_SUBFIELD','a');

    /**
     * Definição do subcampo Marc para codigo da unidade
     */
     define('MARC_KARDEX_LIBRARY_UNIT_ID_SUBFIELD','b');

    /**
     * Definição do subcampo Marc para tipo de aquisição
     */
     define('MARC_KARDEX_ACQUISITION_TYPE_SUBFIELD','c');

    /**
     * Definição do subcampo Marc para vencimento da assinatura
     */
     define('MARC_KARDEX_SIGNATURE_END_SUBFIELD','d');

    /**
     * Definição do subcampo Marc para nota fiscal
     */
     define('MARC_KARDEX_FISCAL_NOTE_SUBFIELD','f');

    /**
     * Definição do subcampo Marc para data da assinatura
     */
     define('MARC_KARDEX_SIGNATURE_DATE_SUBFIELD','h');

    /**
     * Definição do subcampo Marc para data da entrada
     */
     define('MARC_KARDEX_ENTRACE_DATE_SUBFIELD','y');

    /**
     * Determina o id do status Reservado do exemplar
     */
     define('DEFAULT_EXEMPLARY_STATUS_RESERVADO','6');

    /**
     * Valor padrao de formularios do tipo Administrador
     */
     define('FORM_CONTENT_TYPE_ADMINISTRATOR','1');

    /**
     * Valor padrao de formularios do tipo Operator
     */
     define('FORM_CONTENT_TYPE_OPERATOR','2');

    /**
     * Valor padrao de formularios do tipo Busca
     */
     define('FORM_CONTENT_TYPE_SEARCH','3');

    /**
     * Codigo do valor padrão de formularios para busca Avançada
     */
     define('FORM_CONTENT_SEARCH_ADVANCED_ID','2');

    /**
     * Codigo do valor padrão de formularios para busca Aquisição
     */
     define('FORM_CONTENT_SEARCH_ACQUISITION_ID','1');

    /**
     * Valor status para multa paga
     */
     define('DEFAULT_VALUE_FINE_PAY_STATUS','2');

    /**
     * Valor status para multa paga via boleto
     */
     define('DEFAULT_VALUE_FINE_PAYROLL_STATUS','3');

    /**
     * Valor status para multa abonada
     */
     define('DEFAULT_VALUE_FINE_BONUS_STATUS','4');

    /**
     * Valor padrao do grupo de privilégio para empréstimo
     */
     define('DEFAULT_VALUE_PRIVILEGEGROUP_LOAN','1');

    /**
     * Define a mascara da data para o banco.

    dd   = Dia
    mm   = Mês
    yyyy = Ano
     */
     define('MASK_DATE_DB','yyyy-mm-dd');

    /**
     * Define a mascara da hora para o banco.

    hh = Hora
    ii = Minuto
    ss = Segundo
     */
     define('MASK_TIME_DB','hh:ii:ss');

    /**
     * Define a mascara da data para o usuário.

    dd   = Dia
    mm   = Mês
    yyyy = Ano
     */
     define('MASK_DATE_USER','dd/mm/yyyy');

    /**
     * Define a mascara da hora para o usuário.

    hh = Hora
    ii = Minuto
    ss = Segundo
     */
     define('MASK_TIME_USER','hh:ii:ss');

    /**
     * Limita o maior valor que o usuário poderá utilizar para o parâmetro USER_DAYS_BEFORE_EXPIRED.
     */
     define('LIMIT_DAYS_BEFORE_EXPIRED','4');

    /**
     * Código do tipo de reserva local atendida
     */
     define('ID_RESERVETYPE_LOCAL_ANSWERED','3');

    /**
     * Código do tipo de reserva local
     */
     define('ID_RESERVETYPE_LOCAL','1');

    /**
     * Código do tipo de reserva web
     */
     define('ID_RESERVETYPE_WEB','2');

    /**
     * Código do tipo de reserva web atendida
     */
     define('ID_RESERVETYPE_WEB_ANSWERED','4');

    /**
     * define os campos que são data
     */
     define('CATALOGUE_DATE_FIELDS','960.d,960.h,960.i,960.y,008.0-SE,949.y,949.z');

    /**
     * Dimensos maximas da imagem original. Caso alguma imagem ultrpasse estas dimensoes, a imagem sera redimensionada
     */
     define('CATALOGUE_ORIGINAL_IMAGE_DIMENSIONS','800x600');

    /**
     * Dimensos maximas da imagem média. Caso alguma imagem ultrpasse estas dimensoes, a imagem sera redimensionada
     */
     define('CATALOGUE_MIDDLE_IMAGE_DIMENSIONS','400x300');

    /**
     * Dimensos maximas da imagem pequena. Caso alguma imagem ultrpasse estas dimensoes, a imagem sera redimensionada
     */
     define('CATALOGUE_SMALL_IMAGE_DIMENSIONS','200x150');

    /**
     * define a plinilha de coleção
     */
     define('SPREADSHEET_CATEGORY_COLECTION','SE-#');

    /**
     * define a plinilha de coleção
     */
     define('SPREADSHEET_CATEGORY_FASCICLE','SE-4');

    /**
     * Define o id do formato de pesquisa (search format) que será utilizado como padrão para a Circulação de Material.
     */
     define('MATERIAL_MOVIMENT_SEARCH_FORMAT_ID','3');

    /**
     * Relaciona a lista de campos marc que devem aparecer na listagem de exemplares na reserva da pesquisa.
     */
     define('SIMPLE_SEARCH_RESERVE_DETAIL_FIELD_LIST','Unidade de bilioteca=949.b');

    /**
     * Determina se é ou não para esconder/recolher os dados do exemplar na busca simples.
     */
     define('SIMPLE_SEARCH_HIDE_EXEMPLAR','f');

    /**
     * .
     */
     define('HELP_MAIN_CONFIGURATION_LIBRARYUNIT','Ajuda da Unidade de Biblioteca.');

    /**
     * Define o ID do formato de pesquisa que sera exibido na pesquisa z3950
     */
     define('Z3950_SEARCH_FORMAT_ID','6');

    /**
     * Relaciona a lista de campos marc que devem aparecer na listagem de exemplares dos detalhes da pesquisa. Para usuário normal.
     */
     define('SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_USER','Volume=949.v, Tomo=949.t, Unidade de biblioteca=949.b, Tipo de material=949.1, Tipo físico do mater');

    /**
     * Define o texto de ajuda para o campo Default Value do form Spreadsheet.
     */
     define('HELP_FIELD_SPREADSHEET_DEFAULT_VALUES','Para adicionar valores padrão para tag 008,<br>insira um linha semelhante a esta:<br>008=campo1,cam');

    /**
     * Define o texto de ajuda para o campo Work validator do form Spreadsheet.
     */
     define('HELP_FIELD_SPREADSHEET_WORK_VALIDATOR','Exemplo:<br>245.a=required<br>650.a=required<br>901.a=required<br>949.a=unique<br>950.a=required,un');

    /**
     * Define o texto de ajuda para o campo Repeat field validator do form Spreadsheet.
     */
     define('HELP_FIELD_SPREADSHEET_REPEAT_FIELD_VALIDATOR','Exemplo:<br>650.a=required,unique,readonly<br>949.a=required,unique<br>949.b=required<br>949.c=requ');

    /**
     * Seta a primeira opção dos indicadores como valores padrão caso o mesmo valor não exista.
     */
     define('SET_FIRST_OPTION_OF_THE_INDICATOR_AS_DEFAULT','t');

    /**
     * Código do tipo de permuta para envio.
     */
     define('INTERCHANGE_TYPE_SEND','1');

    /**
     * Código do tipo de permuta para recebimento.
     */
     define('INTERCHANGE_TYPE_RECEIPT','2');

    /**
     * Código do estado de permuta CRIADO.
     */
     define('INTERCHANGE_STATUS_CREATED','1');

    /**
     * Código do estado de permuta CARTA ENVIADA.
     */
     define('INTERCHANGE_STATUS_LETTER_SENT','2');

    /**
     * Código do estado de permuta CONFIRMADO.
     */
     define('INTERCHANGE_STATUS_CONFIRMED','3');

    /**
     * Código do estado de permuta AGRADECIDO.
     */
     define('INTERCHANGE_STATUS_GRATEFUL','5');

    /**
     * Código inicial de uma solicitação de alteração de estado de material
     */
     define('REQUEST_CHANGE_EXEMPLARY_STATUS_REQUESTED','1');

    /**
     * Código de aprovação de uma solicitação de alteração de estado de material
     */
     define('REQUEST_CHANGE_EXEMPLARY_STATUS_APROVED','2');

    /**
     * Código de reprovação de uma solicitação de alteração de estado de material
     */
     define('REQUEST_CHANGE_EXEMPLARY_STATUS_REPROVED','3');

    /**
     * Código de conclusão de uma solicitação de alteração de estado de material
     */
     define('REQUEST_CHANGE_EXEMPLARY_STATUS_CONCLUDE','4');

    /**
     * Código de cancelamento de uma solicitação de alteração de estado de material
     */
     define('REQUEST_CHANGE_EXEMPLARY_STATUS_CANCEL','5');

    /**
     * Código de confirmação de uma solicitação de alteração de estado de material
     */
     define('REQUEST_CHANGE_EXEMPLARY_STATUS_CONFIRMED','6');

    /**
     * Define uma chave para criação do hash de recibos.
     */
     define('HASH_KEY','kcwcvbk3');

    /**
     * Seta se é para gerar log de envio de emails.
     */
     define('MAIL_LOG_GENERATE','t');

    /**
     * Seta o nome do arquivo de log.
     */
     define('MAIL_LOG_FILE_NAME','gnuteca3-mail.log');

    /**
     * Etiqueta marc de notas gerais
     */
     define('MARC_GERAL_NOTE_TAG','500.a');

    /**
     * Define a quantidade de dias (data atual - dias definidos) no formulario de processo Reorganizar fila de reserva.
     */
     define('RESERVE_QUEUE_DAYS','0');

    /**
     * Define o assunto do email que será enviado para o usuário quando sua reserva for atendida.
     */
     define('EMAIL_RESERVE_ANSWERED_SUBJECT','Aviso de reserva');

    /**
     * Caso verdadeiro, usa LDAP para autenticar na minha biblioteca, usando configuração de conexão de LDAP definida no conf
     */
     define('MY_LIBRARY_AUTHENTICATE_LDAP','f');

    /**
     * Define o assunto do email que sera enviado para o administrador informando o cancelamento de uma solicitação de mudança de estado.
     */
     define('EMAIL_CANCEL_SUBJECT_REQUEST_CHANGE_EXEMPLARY_STATUS','Cancelamento de Congelamento');

    /**
     * Codigo do estado da multa Paga
     */
     define('ID_FINESTATUS_PAYED','2');

    /**
     * Especifica as opções que fazem parte do campo caracteres na geração do código de barras.

    VALOR<espaço>Descrição
     */
     define('BAR_CODE_CHARACTERS','0 Não fixos
    6 6
    8 8
    10 10
    12 12
    14 14
    16 16
    18 18');

    /**
     * Determina o id do status Disponivel do exemplar
     */
     define('DEFAULT_EXEMPLARY_STATUS_DISPONIVEL','1');

    /**
     * Determina o id do status Desaparecido do exemplar
     */
     define('DEFAULT_EXEMPLARY_STATUS_DESAPARECIDO','3');

    /**
     * Determina o id do status Danificado do exemplar
     */
     define('DEFAULT_EXEMPLARY_STATUS_DANIFICADO','4');

    /**
     * Determina o id do status Emprestado do exemplar
     */
     define('DEFAULT_EXEMPLARY_STATUS_EMPRESTADO','5');

    /**
     * Determina o id do status Emprestado do exemplar
     */
     define('DEFAULT_EXEMPLARY_STATUS_DESCARTADO','9');

    /**
     * Determina o id do status em Processamento do exemplar
     */
     define('DEFAULT_EXEMPLARY_STATUS_PROCESSANDO','15');

    /**
     * Codigo do estado da multa Paga via boleto
     */
     define('ID_FINESTATUS_BILLET','3');

    /**
     * Codigo do estado da multa Abonada
     */
     define('ID_FINESTATUS_EXCUSED','4');

    /**
     * define os campos que so lookup
     */
     define('CATALOGUE_LOOKUP_FIELDS','947.a=SupplierType:DescON
    090.b=Cutter:DescOFF
    090.a=Classification:DescOFF
    949.q=CostCenter:DescON');

    /**
     * Definicao do campo MARC marc para informações de edição
     */
     define('MARC_EDITION_TAG','250.a');

    /**
     * Definicao do campo MARC marc para informações de edição
     */
     define('MARC_PUBLICATION_DATE_TAG','260.c');

    /**
     * Definição do campo Marc para o título.
     */
     define('MARC_SECUNDARY_TITLE_TAG','440.a');

    /**
     * Código do tipo de reserva local em estado inicial
     */
     define('ID_RESERVETYPE_LOCAL_INITIAL_STATUS','5');

    /**
     * Define materialTypeId para coleção de periódico
     */
     define('MATERIAL_TYPE_ID_PERIODIC_COLLECTION','23');

    /**
     * Define as Opções de pesquisa z3950. Fonte: http://www.loc.gov/z3950/agency/defns/bib1.html
     */
     define('Z3950_SEARCH_OPTIONS','1016 = Todos os Campos;
    7 = ISBN (020);
    14 = CDU (080);
    21 = Subject;
    1000 = Author and title;
    1003');


    /**
     * define os campos que são arrayField
     */
     define('CATALOGUE_ARRAY_FIELDS','949.q');

    /**
     * Definicao do campo MARC marc para nome do servidor (endereço da página Web)
     */
     define('MARC_NAME_SERVER','856.u');

    /**
     * Constante que define se deve filtrar operadores dentro da catalogação
     */
     define('CATALOGUE_FILTER_OPERATOR','FALSE');

    /**
     * Tipo de formato padrÃ£o a ser retornado no webservice de material (informaÃ§Ã£o do material)
     */
     define('WEB_SERVICE_MATERIAL_DEFAULT_SEARCH_FORMAT_ID','8');

    /**
     * Valor inicial status da multa
     */
     define('DEFAULT_VALUE_FINE_INITIAL_STATUS','3');

    /**
     * Estado de material congelado
     */
     define('DEFAULT_EXEMPLARY_STATUS_CONGELADO','2');

    /**
     * Ajuda da pesquisa avançada, altera o código final para as pesquisas
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_2','<B><CENTER>Através desta busca é possível pesquisar em vários campos ao mesmo tempo.</B></CENTER>
    <');

    /**
     * Pesquisa Aquisição.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_5','<B><CENTER>Busca os materiais cadastrados num determinado período.</B></CENTER>
    Deve-se utilizar os');

    /**
     * Pesquisa periódico.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_6','<B><CENTER>Pesquisa de periódicos com títulos iniciados pela letra marcada.</B></CENTER>
    É necessár');

    /**
     * Etiqueta marc de extensão (normalmente contagem de páginas)
     */
     define('MARC_EXTENSION_TAG','300.a');

    /**
     * Conteúdo de ajuda que será exibido quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_MYFINE','<CENTER><B>Aqui o usuário pode visualizar todas as multas por atraso na devolução de materiais.</CE');

    /**
     * Conteúdo de ajuda que será exibido quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_MYINFORMATION','Aqui o usuário pode visualizar seus dados de contato, além de seu vínculo com a Univates.');

    /**
     * Define o modelo das obras que é anexado ao recibo de empréstimo
     */
     define('LOAN_RECEIPT_WORK','| <pad 44| $SP | RIGHT>CÓDIGO DO EXEMPLAR: $ITEM_NUMBER</pad> |$LN
    | <pad 44| $SP | RIGHT>TÍTULO: $');

    /**
     * Conteúdo de ajuda que será exibida quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH','<center><b>Ajuda da pesquisa</b></center>
    <img SRC="MIOLO_GET_IMAGE(ConteudoFormulario.png)" align=');

    /**
     * Conteúdo de ajuda que será exibida quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_1','<center><b>Ajuda da pesquisa</b></center>
    <img SRC="MIOLO_GET_IMAGE(ConteudoFormulario.png)" align=');

    /**
     * Etiqueta marc de linguagem.
     */
     define('MARC_LANGUAGE_TAG','041.a');

    /**
     * Define o modo de impressão.

    Opções:
    1 = Impressão por socket
    2 = Impressão pelo navegador
     */
     define('PRINT_MODE','1');

    /**
     * Conteúdo de ajuda que será exibido quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_CONGELADO','Aqui o usuário pode visualizar os materiais congelados pelos professores. São materiais que não pod');

    /**
     * Conteúdo de ajuda que será exibido quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_FAVORITE','<CENTER><B>Aqui estão armazenados todos os materiais que o usuário adicionou aos favoritos.</CENTER');

    /**
     * Conteúdo de ajuda que será exibido quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_INTERESTSAREA','<CENTER><B>Aqui o usuário pode marcar as áreas de seu interesse e assim estar sempre atualizado sob');

    /**
     * Conteúdo de ajuda que será exibido quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_MYLOAN','<CENTER><B>Aqui o usuário pode visualizar todos materiais que retirou nas unidades de bibliotecas d');

    /**
     * Marc tag that represents ISBN identifier.
     */
     define('MARC_ISBN_TAG','020.a');

    /**
     * Conteúdo de ajuda que será exibido quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_MYPENALTY','<CENTER><B>Aqui o usuário pode visualizar as penalidades recebidas.</CENTER></B>
    Quando uma penalid');

    /**
     * Conteúdo de ajuda que será exibido quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_MYRENEW','Aqui o usuário pode renovar os materiais que retirou na biblioteca. Não é possível renovar itens qu');

    /**
     * Conteúdo de ajuda que será exibido quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_MYRESERVESSEARCH','<center><B>Aqui o usuário pode verificar a situação de suas reservas.</B></center>
    As reservas pode');

    /**
     * Conteúdo de ajuda que será exibido quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_PERSONCONFIG','<CENTER><B>Configurações de avisos</CENTER></B>
    Aqui o usuário pode configurar:
    <UL><LI><B>Enviar E');

    /**
     * Conteúdo de ajuda que será exibido quando usuário clicar no botão Ajuda/Help do menu da pesquisa. Repare que todo form pode ter sua configuração de Help criando a preferência do sistema HELP_ + o action do handler.
     */
     define('HELP_MAIN_SEARCH_SIMPLESEARCH_RESERVESHISTORY','<CENTER><B>Aqui o usuário pode visualizar o histórico de suas reservas.</CENTER></B>
    É possível fi');

    /**
     * Assunto do email de recebimento do intercambio
     */
     define('INTERCHANGE_MAIL_RECEIPT_SUBJECT','Recebimento de permuta');

    /**
     * Conteúdo do email de recebimento
     */
     define('INTERCHANGE_MAIL_RECEIPT_CONTENT','Recebemos e agradecemos: $LN $LN
    $MATERIALS
    $LN $LN
    Atenciosamente,$LN
    -- $LN
    Ana Paula Lisboa Mont');

    /**
     * Constante que define se tem pagamento de multa via boleto ou não
     */
     define('LOAN_FINE_PAYMENT_BOLETO','t');

    /**
     * Modelo utilizado na geração da carta do intercâmbio
     */
     define('INTERCHANGE_LETTER_MODEL','<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
    <HTML>
    <HEAD>
        <META HTTP-EQUIV="CON');

    /**
     * Conteúdo do e-mail enviado para as bibliotecas sobre empréstimo entre unidades (CANCELAMENTO)
     */
     define('EMAIL_LOANBETWEENLIBRARY_CANCEL_CONTENT','Informamos que a biblioteca $LIBRARY_UNIT_DESCRIPTION cancelou o pedido de empréstimo para os segui');

    /**
     * define os campos que são texto
     */
     define('CATALOGUE_MULTILINE_FIELDS','949.w,960.w');

    /**
     * Listar a opção Não receber e enviar recibo nos campos Comprovantes de empréstimo e Comprovantes de devolução no formulário Configuração do usuário da Minha biblioteca.
     */
     define('MARK_DONT_PRINT_SEND_RECEIPT','f');

    /**
     * Os campos válidos são:
    USER_DELAYED_LOAN
    USER_SEND_DELAYED_LOAN
    USER_NOTIFY_AQUISITION
    USER_SEND_NOTIFY_AQUISITION
    USER_DAYS_BEFORE_EXPIRED
    USER_SEND_DAYS_BEFORE_EXPIRED
    CONFIGURE_RECEIPT_LOAN
    CONFIGURE_RECEIPT_RETURN
    USER_SEND_RECEIPT_RENEW_WEB

    Legenda:
    W: Libera o campo para leitura e escrita.
    R: Libera apenas para leitura.
    I: Não mostra o campo.

    O ponto-e-vírgula separa os campos.
    A vírgula separa a preferência e seu valor da legenda.
    O igual separa o valor da preferência.
     */
     define('USER_CONFIG','USER_SEND_DELAYED_LOAN=w|Enviar avisos de materiais em atraso por e-mail?
    USER_DELAYED_LOAN=w|Quant');

    /**
     * Mensagem a ser mostrada no topo da tela das áreas de interesse da Minha Biblioteca
     */
     define('LABEL_INTEREST_AREA','<CENTER><B>Selecione abaixo as áreas de seu interesse e no menu configurações habilite a opção "Env');

    /**
     * Mensagem a ser mostrada no topo da tela dos congelados da Minha Biblioteca
     */
     define('LABEL_CONGELADO','<CENTER><B>Lista de materiais congelados pelos professores</CENTER></B><BR>');

    /**
     * Esta preferência definirá como os usuários do LDAP serão inseridos. A configuração se dará com uma base por linha, exemplo:
    base_1;nome=<tagDoLdap>;email=<tagDoLdap>;login=<tagDoLdap>;vinculo=1;validade=12/12/2012
    base_2;nome=<tagDoLdap>;email=<tagDoLdap>;login=<tagDoLdap>;vinculo=2;validade=11/11/2011
     */
     define('MY_LIBRARY_LDAP_INSERT_USER','base_1;nome=displayname;email=mail;login=uid;vinculo=1;validade=12/12/2012
    base_2;nome=displayname');

    /**
     * Título do formulário de Requisições das Pesquisas
     */
     define('SEARCH_REQUEST_TITLE','Requisitar congelamento');

    /**
     * Define integração com Google Books
     */
     define('GB_INTEGRATION','t');

    /**
     * Define se as buscas de material utilizam ou não prefixo e sufixo automaticamente
     */
     define('MATERIAL_SEARCH_USE_PREFIX_SUFFIX','t');

    /**
     * Mostra ou esconde ações extras na pesquisa. É importante notar que todas estas ações podem ser acessadas na janela de detalhes.
     */
     define('SIMPLE_SEARCH_SHOW_EXTRA_ACTIONS','t');

    /**
     * Integração com biblioteca nacional
     */
     define('FBN_INTEGRATION','t');

    /**
     * Ativa/desativa avaliações na pesquisa para usuário.
     */
     define('SIMPLE_SEARCH_EVALUATION','t');

    /**
     * Define se é para enviar avisos por e-mail antes de vencer os materiais.
     */
     define('USER_SEND_DAYS_BEFORE_EXPIRED','t');

    /**
     * Valor padrão que indica a quantidade de dias antes em que o usuário deva ser informado da devolução de um material. Este valor não pode ultrapassar o valor definido em LIMIT_DAYS_BEFORE_EXPIRED
     */
     define('USER_DAYS_BEFORE_EXPIRED','2');

    /**
     * Define o sufixo no assunto do e-mail que será enviado ao usuário para avisá-lo da devolução do material emprestado. Complementa o parâmetro EMAIL_SUBJECT_PREFIX.
     */
     define('EMAIL_RETURN_SUBJECT','Prazo de empréstimo');

    /**
     * Define o nome do remetende para envio de e-mails.
     */
     define('EMAIL_FROM_NAME','Gnuteca Devel');

    /**
     * Define o remetente dos emails.
     */
     define('EMAIL_FROM','gnutecadevel@gmail.com');

    /**
     * Prefixo utilizado para a descrição do sistema no assunto dos e-mails.
     */
     define('EMAIL_SUBJECT_PREFIX','[Gnuteca]');

    /**
     * Define s quatidade de cópias que será impressa dos recibos de empréstimo e devolução
     */
     define('RECEIPT_COPIES_AMOUNT','1');

    /**
     * Indica se é para enviar e-mail de agradecimento diretamente para fornecedor ou abrir uma tela com conteúdo e destinatário para usuário personalizar a mensagem
     */
     define('INTERCHANGE_MAIL_RECEIPT_AUTOSEND','t');

    /**
     * Define o id do formato de pesquisa (search format) que será utilizado como padrão para a pesquisa.
     */
     define('_ID','2');

    /**
     * Define o conteúdo do e-mail que será enviado para o usuário com a data prevista para devolução do empréstimo.
    Variáveis aceitas:
    $USER_NAME - Nome do usuário
    $MATERIAL_TITLE - Descrição do material
    $ITEM_NUMBER - registro do material
    $RETURN_FORECAST_DATE - Data prevista para devolução
    $LIBRARY_UNIT_DESCRIPTION - Library unit description
     */
     define('EMAIL_RETURN_CONTENT','Prezado(a) $USER_NAME, $LN $LN
    Seu empréstimo intitulado "$MATERIAL_TITLE" ($ITEM_NUMBER) irá vence');

    /**
     * Valor padrão que indica a quantidade de emails e o intervalo de dias para comunicar os usuários que estão com empréstimos atrasados. Ex: 5;7 - enviará 5 emails com um intervalo de 7 dias cada. 7;0 - enviará 7 email sem intervalo de dias entre eles.
     */
     define('USER_DELAYED_LOAN','3;7');

    /**
     * Define o titulo de email que envia o resultado das notificações de término de requisição para administrador.
     */
     define('EMAIL_ADMIN_NOTIFY_END_REQUEST_RESULT_SUBJECT','Comunicação das notificações de término de requisição.');

    /**
     * Define o endereço de e-mail da biblioteca. Esse endereço é utilizado para envio dos e-mails de requisição de troca de estado de material.
     */
     define('EMAIL_ADMIN_REQUEST_CHANGE_EXEMPLARY_STATUS','admbiblio@univates.br');

    /**
     * Conteudo do e-mail enviado para as bibliotecas sobre o cancelamento de uma solicitação de troca de estado;
     */
     define('EMAIL_CANCEL_CONTENT_REQUEST_CHANGE_EXEMPLARY_STATUS','O congelamento $REQUEST_ID foi cancelado.
    ');

    /**
     * Determina que as requisições de troca de estado terão datas pre definidas por semestre
     */
     define('REQUEST_CHANGE_EXEMPLARY_STATUS_BY_SEMESTER','t');

    /**
     * Determina os peridos validos das requisições; Pode ser definido N periodos.

    Exemplos:

    A=
    StartDate:01/01,
    EndDate:30/06,
    Starting:01/12;
    B=
    StartDate:01/07,
    EndDate:31/12,
    Starting:01/06;

     */
     define('REQUEST_CHANGE_EXEMPLARY_STATUS_SEMESTER_PERIOD','A=
    StartDate:01/01,
    EndDate:30/06,
    Starting:01/12;
    B=
    StartDate:01/07,
    EndDate:31/01,
    Starting:01/0');

    /**
     * Mensagem que aviso sobre agendamento no congelamento
     */
     define('REQUEST_CHANGE_STATUS_SCHEDULED_MSG','ATENÇÃO: Alguns exemplares poderão ser agendados.');

    /**
     * Tamanho padrão para campos ID
     */
     define('FIELD_ID_SIZE','8');

    /**
     * Tamanho padrão para campos DESCRIPTION
     */
     define('FIELD_DESCRIPTION_SIZE','38');

    /**
     * Tamanho padrão para campos TIME
     */
     define('FIELD_TIME_SIZE','7');

    /**
     * Define se os envios de email são testes
     */
     define('EMAIL_TESTING','t');

    /**
     * Define o email que recebera os testes.
    admbiblio2@univates.br
     */
     define('EMAIL_TEST_RECEIVE','gnutecadevel@gmail.com');

    /**
     * Define porta que será utilizadA, pelo Gnuteca, para envio de mensagens
     */
     define('EMAIL_PORT','465');

    /**
     * Define o user SMTP que será utilizado, pelo Gnuteca, para envio de mensagens.
     */
     define('EMAIL_USER','gnutecadevel@gmail.com');

    /**
     * Define senha do user que será utilizado, pelo Gnuteca, para envio de mensagens.
     */
     define('EMAIL_PASSWORD','gnutecadevel123');

    /**
     * Mensagem a ser mostrada no topo de configurações pessoais da Minha Biblioteca
     */
     define('LABEL_PERSON_CONFIG','<CENTER><B>IMPORTANTE: O não recebimento de algum dos avisos da biblioteca não isenta o aluno da re');

    /**
     * Define o titulo de email que envia o resultado das devoluções atrasadas para o administrador
     */
     define('EMAIL_ADMIN_DELAYED_LOAN_RESULT_SUBJECT','Comunicação das devoluções atrasadas');

    /**
     * Conteudo do e-mail que é enviado para o administrador com o resultado das devoluções atrasadas.
     */
     define('EMAIL_ADMIN_DELAYED_LOAN_RESULT_CONTENT','Segue abaixo o resultado do comunicado de devoluções.$LN $CONTENT');

    /**
     * Define o endereço de e-mail da biblioteca. Esse endereço é utilizado para envio dos e-mails de notificação de aquisições.
     */
     define('EMAIL_ADMIN_NOTIFY_ACQUISITION','admbiblio@univates.br ');

    /**
     * Conteudo do e-mail que é enviado para o administrador com o resultado das notificações de término de requisição.
     */
     define('EMAIL_ADMIN_NOTIFY_END_REQUEST_RESULT_CONTENT','Segue abaixo o resultado do comunicado de término de requisições.$LN $CONTENT');

    /**
     * Define o assunto do email que sera enviado para o professor informando o encerramento do prazo de congelamento de material.
     */
     define('EMAIL_COMUNICA_SOLICITANTE_TERMINO_REQUISICAO_SUBJECT','Encerrando período de congelamento de material');

    /**
     * Conteudo do e-mail enviado para o professor sobre o encerramento do prazo de congelamento de material
     */
     define('EMAIL_COMUNICA_SOLICITANTE_TERMINO_REQUISICAO_CONTENT','Prezado(a) $REQUESTOR_NAME, $LN$LN

    Sua solicitação de congelamento, descrita abaixo, está encerran');

    /**
     * Define se o envio de email é authenticado ou não.
     */
     define('EMAIL_AUTHENTICATE','t');

    /**
     * Ao tentar reservar material que está no estado inicial, mostra este aviso
     */
     define('MSG_INITIAL_STATUS','O(s) exemplar(es) está(ão) disponível(eis). Deseja efetuar a reserva?
    <BR>
    <b>Atenção:</b> É necess');

    /**
     * Tamanho padrão (em pixel ou em percentual) para LABELS;
    Ex:
     180px
    18%
     */
     define('FIELD_LABEL_SIZE','200px');

    /**
     * Cobrar multas em feriados
     */
     define('CHARGE_FINE_IN_THE_HOLIDAY','t');

    /**
     * Cobrar multa quando a biblioteca está fechada
     */
     define('CHANGE_FINE_WHEN_THE_LIBRARY_UNIT_IS_CLOSED','f');

    /**
     * Define o recibo de devolução
     */
     define('FINE_RECEIPT','$LN
    +----------------------------------------------+$LN
    | <pad 44| $SP | RIGHT>Biblioteca: $LIBRARY');

    /**
     * Exemplos: 24*,856.a,856.u,901.a,902.b,900*
    901.* = pega todas as tags da etiqueta 901 (901.a,901.b)
    990* = pega as tags de 901.* a 909.*
    9** = pega todas as tags da etiqueta 900
     */
     define('Z3950_IGNORAR_TAGS','9**');

    /**
     * Define o código do relatório de gerencia de dicionários
     */
     define('REPORT_ID_DICTIONARY','57');

    /**
     * Define o modelo das obras que é anexado ao recibo de devolução
     */
     define('FINE_RECEIPT_WORK','
    | <pad 44| $SP | RIGHT>CODIGO DA MULTA: $FINE_ID</pad> |$LN
    | <pad 44| $SP | RIGHT>CODIGO DO EXEMP');

    /**
     * Nome da classe que fará a checagem se o usuário terá permissão de retirar materiais na biblioteca. Não será executada esta verificação se o valor estiver em branco ou incorreto. Opções válidas: BusPersonLibraryUnit
     */
     define('CLASS_USER_ACCESS_IN_THE_LIBRARY','BusNotPersonLibraryUnit');

    /**
     * Permite cadastrar novas pessoas e alterar seus dados
     */
     define('CHANGE_WRITE_PERSON','t');

    /**
     * Permite, ou não a execução de tarefas em segundo plano.
     */
     define('EXECUTE_BACKGROUND_TASK','t');

    /**
     * Nível de registro de acesso para todo o sistema. Valores possíveis são 0-desligado, 1-normal, 2-máximo
     */
     define('ANALYCTS_LOGLEVEL_INNER','1');

    /**
     * Nível de registro de acesso para pesquisa. Valores possíveis são 0-desligado, 1-normal, 2-máximo
     */
     define('ANALYCTS_LOGLEVEL_OUTER','1');

    /**
     * Define o sufixo no assunto do e-mail que será enviado ao usuário anexando o recibo de empréstimo e/ou renovação. Complementa o parâmetro EMAIL_LOAN_RENEW_RECEIPT_CONTENT.
     */
     define('EMAIL_LOAN_RENEW_RECEIPT_SUBJECT','Recibo de empréstimo/renovação');

    /**
     * Define o recibo de devolução
     */
     define('RETURN_RECEIPT','$LN
    +----------------------------------------------+$LN
    | <pad 44| $SP | RIGHT>Biblioteca: $LIBRARY');

    /**
     * Define o sufixo do assunto do email de finalização da solicitação de compra
     */
     define('EMAIL_PURCHASE_REQUEST_FINALIZE_SUBJECT','Aviso de aprovação de solicitação de compra');

    /**
     * Csv que monta os links para o usermenu da persquisa simples. Separado por linha, depois por ;,
     */
     define('GNUTECA_USER_MENU_LIST','Renovar; javascript:miolo.doAjax(\'subForm\',\'MyRenew\',\'__mainForm\'); renew-16x16.png
    Minhas reserva');

    /**
     * Define se a opção de imprimir recibo de empréstimo sai marcada.
     */
     define('MARK_PRINT_RECEIPT_LOAN','t');

    /**
     * Define se a opção de enviar recibo de emprestimo sai marcada.
     */
     define('MARK_SEND_LOAN_MAIL_RECEIPT','f');

    /**
     * Define o recibo de empréstimo
     */
     define('LOAN_RECEIPT','$LN
    +----------------------------------------------+$LN
    | <pad 44| $SP | RIGHT>Biblioteca: $LIBRARY');

    /**
     * Define o sufixo no assunto do e-mail que será enviado ao usuário anexando o recibo de devolução. Complementa o parâmetro EMAIL_RETURN_RECEIPT_CONTENT.
     */
     define('EMAIL_RETURN_RECEIPT_SUBJECT','Recibo de devolução');

    /**
     * Define o endereço de e-mail da biblioteca. Esse endereço é utilizado para envio dos e-mails de emprestimo entre bibliotecas
     */
     define('EMAIL_ADMIN_LOAN_BETWEEN_LIBRARY','admbiblio@univates.br');

    /**
     * Assunto do e-mail enviado para as bibliotecas sobre empréstimo entre unidades
     */
     define('EMAIL_LOANBETWEENLIBRARY_SUBJECT','Empréstimo entre unidades - $STATUS');

    /**
     * Mostrar botão Obter CSV nas grids da Minha Biblioteca
     */
     define('CSV_MYLIBRARY','f');

    /**
     * Define o SearchFormatId para Administração em geral.
     */
     define('ADMINISTRATION_SEARCH_FORMAT_ID','5');

    /**
     * Conteúdo da carta de envio para o intercambio
     */
     define('INTERCHANGE_LETTER_SEND_CONTENT','Prezado $CONTACT_NAME, $LN $LN
    Estamos enviando os seguintes materiais:$LN
    $MATERIALS
    $LN
    Atenciosa');

    /**
     * Mensagem a ser mostrada no topo de dados pessoais da Minha Biblioteca
     */
     define('LABEL_PERSON_DATA','<CENTER><B>Para alterar seus dados acesse:</B>
    <A HREF="https://www.univates.br/universounivates/in');

    /**
     * Mensagem na tela Renovar da Minha biblioteca
     */
     define('LABEL_RENEW','<CENTER><B>Para efetuar a renovação, selecione os materiais e clique no botão renovar. Na janela de');

    /**
     * Título do formulário de Requisições da Minha Biblioteca
     */
     define('MYLIBRARY_REQUEST_TITLE','Congelados');

    /**
     * Define se permite ou não pesquisar em todas as bibliotecas para o operador do sistema.
     */
     define('SIMPLE_SEARCH_ALL_LIBRARYS_OPERATOR','t');

    /**
     * Define se é para enviar avisos de materiais em atraso por e-mail.
     */
     define('USER_SEND_DELAYED_LOAN','t');

    /**
     * Define o endereço de e-mail da biblioteca. Esse endereço é utilizado para envio dos e-mails de devolução atrasada.
     */
     define('EMAIL_ADMIN_DELAYED_LOAN','');

    /**
     * Conteudo do e-mail enviado para as bibliotecas sobre empréstimo entre unidades (REQUISICAO)
     */
     define('EMAIL_LOANBETWEENLIBRARY_REQUEST_CONTENT','Informamos que a biblioteca $LIBRARY_UNIT_DESCRIPTION requisitou empréstimo para os seguintes mater');

    /**
     * Conteúdo do e-mail enviado para as bibliotecas ao Aprovar ou Reprovar um empréstimo entre unidade
     */
     define('EMAIL_LOANBETWEENLIBRARY_CONFIRMLOAN_CONTENT','Informamos que a biblioteca $LIBRARY_UNIT_DESCRIPTION $ACTION o empréstimo para os seguintes materi');

    /**
     * Conteúdo do e-mail enviado para as bibliotecas ao encaminhar materiais para devolução
     */
     define('EMAIL_LOANBETWEENLIBRARY_RETURNMATERIAL_CONTENT','Informamos que a biblioteca $LIBRARY_UNIT_DESCRIPTION encaminhou para devolução os seguintes materi');

    /**
     * Define o SearchFormatId usado no formulário de listagem de favoritos.
     */
     define('FAVORITES_SEARCH_FORMAT_ID','4');

    /**
     * Mensagem a ser mostrada no topo da tela das Minhas reservas da Minha Biblioteca
     */
     define('LABEL_MY_RESERVES','<CENTER><B>Esta é a lista de suas reservas em aberto. Se a reserva estiver com estado "Atendida" ou');

    /**
     * Define se é permitido a pessoa (usuário) pesquisar em todas unidades na pesquisa simples.
     */
     define('SIMPLE_SEARCH_ALL_LIBRARYS_PERSON','f');

    /**
     * Define se é para mostrar campo Condição dos Termos da pesquisa.
     */
     define('SIMPLE_SEARCH_SHOW_TERM_CONDITION','f');

    /**
     * Define o id do formato de pesquisa (search format) que será utilizado como padrão para a pesquisa.
     */
     define('SIMPLE_SEARCH_SEARCH_FORMAT_ID','1');

    /**
     * Define o id do formato de pesquisa (search format) que será utilizado nos detalhes do artigo.
     */
     define('SIMPLE_SEARCH_SEARCH_FORMAT_ID_DETAIL_ARTICLE','7');

    /**
     * Define o id do formato de pesquisa (search format) que será utilizado nos detalhes do fasciculo.
     */
     define('SIMPLE_SEARCH_SEARCH_FORMAT_ID_DETAIL_FASCICLE','7');

    /**
     * Ordenação padrão para a pesquisa no formulario. A configuracao deve ser: CodigoDoCampoPesquisavel,TipoOrdenacao - Ex: 1,ASC
     */
     define('SIMPLE_SEARCH_DEFAULT_ORDER','1,ASC');

    /**
     * Texto que aparece no formulário de pesquisa, acima da escolha de formatos de pesquisa
     */
     define('SIMPLE_SEARCH_SEARCH_FORMAT_STRING','Formato de Visualização da Pesquisa:');

    /**
     * Relaciona as planilhas a serem excluídas na pesquisa simples, separe por linha nova para mais de um item: Ex.: SE,4
     */
     define('SIMPLE_SEARCH_EXCLUDE_SPREEDSHET','SE,4');

    /**
     * Id do estado de exemplares a ignorar na pesquisa.
     */
     define('SIMPLE_SEARCH_EXCLUDE_EXEMPLARY_STATUS','3,4,9');

    /**
     * Define a quantidade máxima de indices a serem mostrados nas pesquisas
     */
     define('SIMPLE_SEARCH_MAX_LIMIT','100');

    /**
     * Texto que aparece na tela de login do formulário da busca Simples.
     */
     define('SIMPLE_SEARCH_LOGIN_STRING','Por favor faça seu login abaixo:');

    /**
     * Assunto do e-mail enviado para usuário na Busca Simples, contendo o relatório PDF gerado em anexo.
     */
     define('EMAIL_SIMPLESEARCH_REPORT_SUBJECT','Lista de materiais');

    /**
     * Conteúdo do e-mail enviado para usuário na Busca Simples, contendo o relatório PDF gerado em anexo.
     */
     define('EMAIL_SIMPLESEARCH_REPORT_CONTENT','Prezado(a), $LN $LN
    Segue em anexo o relatório contendo informações dos materiais selecionados na ');

    /**
     * Define o modelo das obras que é anexado ao recibo de devolução
     */
     define('RETURN_RECEIPT_WORK','| <pad 44| $SP | RIGHT>CÓDIGO DO EXEMPLAR: $ITEM_NUMBER</pad> |$LN
    | <pad 44| $SP | RIGHT>TÍTULO: $');

    /**
     * Conteudo do email que sera enviado com o recibo em anexo.
     */
     define('EMAIL_RETURN_RECEIPT_CONTENT','Prezado(a) $USER_NAME, $LN $LN
    Segue em anexo o seu recibo de devolução. $LN $LN
    Atenciosamente,$L');

    /**
     * Define o conteudo do e-mail que será enviado para o usuário quando sua reserva for atendida. O Gnuteca 2 após o envio desta mensagem, altera, automaticamente, o estado da reserva de Atendida para Comunicada.
    Variáveis aceitas:
    $USER_NAME - Nome do usuário
    $MATERIAL_TITLE - Descrição do material
    $RESERVE_WITHDRAWAL_DATE - Data limite de retirada
     */
     define('EMAIL_RESERVE_ANSWERED_CONTENT','Prezado(a) $USER_NAME, $LN $LN
    Sua reserva de código $RESERVE_CODE, intitulada "$MATERIAL_TITLE", j');

    /**
     * Define o titulo de email que envia o resultado do comunicado de reservas atendidas.
     */
     define('EMAIL_CANCEL_RESERVE_COMUNICA_SOLICITANTE_SUBJECT','Aviso de Reserva');

    /**
     * Define o endereço de e-mail da biblioteca. Esse endereço é utilizado para envio dos e-mails de devolução.
     */
     define('EMAIL_ADMIN_DEVOLUTION','admbiblio@univates.br');

    /**
     * Define o titulo de email que envia o resultado das devoluções para o administrador.
     */
     define('EMAIL_ADMIN_DEVOLUTION_RESULT_SUBJECT','Comunicação das devoluções.');

    /**
     * Conteudo do e-mail que é enviado para o administrador com o resultado das devoluções.
     */
     define('EMAIL_ADMIN_DEVOLUTION_RESULT_CONTENT','Segue abaixo o resultado do comunicado de devoluções.$LN $CONTENT');

    /**
     * Número de dias antes e após o final do período, onde será liberado a renovação
     */
     define('REQUEST_CHANGE_DAYS','30');

    /**
     * Conteudo do email que sera enviado com o recibo em anexo.
     */
     define('EMAIL_LOAN_RENEW_RECEIPT_CONTENT','Prezado(a) $USER_NAME,$LN $LN Segue em anexo o seu recibo de empréstimo. $LN $LN

    Atenciosamente,$L');

    /**
     * Tempo, em minutos, de limite da operação de empréstimo.
     */
     define('OPERATION_PROCESS_TIME','3');

    /**
     * Define se a opção de imprimir recibo de devolução sai marcada.
     */
     define('MARK_PRINT_RECEIPT_RETURN','t');

    /**
     * Define o conteúdo do e-mail que será enviado para o usuário com a data prevista para devolução do empréstimo.
    Variáveis aceitas:
    $USER_NAME - Nome do usuário
    $MATERIAL_TITLE - Descrição do material
    $ITEM_NUMBER - registro do material
    $LIBRARY_UNIT_DESCRIPTION - Library unit description
     */
     define('EMAIL_CANCEL_RESERVE_COMUNICA_SOLICITANTE_CONTENT','Prezado(a) $USER_NAME, $LN $LN
    Seu reserva intitulado "$MATERIAL_TITLE" ($ITEM_NUMBER) foi cancelad');

    /**
     * Define o sufixo no assunto do e-mail que será enviado ao usuário para avisá-lo do atraso da devolução do material emprestado. Complementa o parâmetro EMAIL_SUBJECT_PREFIX.
     */
     define('EMAIL_DELAYED_LOAN_SUBJECT','Empréstimo atrasado');

    /**
     * Define o servidor SMTP que será utilizado, pelo Gnuteca, para envio de mensagens.
     */
     define('EMAIL_SMTP','ssl://smtp.gmail.com');

    /**
     * Define o conteudo do e-mail que será enviado para o usuário quando seu empréstimo estiver em atraso.
    Variáveis aceitas:
    $USER_NAME - Nome do usuário
    $ITEM_NUMBER - registro do material
    $MATERIAL_TITLE - Título do material
    $RETURN_DATE - Data prevista para devolução
    $LIBRARY_UNIT_DESCRIPTION - Nome da Biblioteca.

     */
     define('EMAIL_DELAYED_LOAN_CONTENT','Prezado(a) $USER_NAME, $LN
    Seu empréstimo ($ITEM_NUMBER) intitulado "$MATERIAL_TITLE", cuja data d');

    /**
     * Quebra de linha dos avisos enviados por e-mail
     */
     define('EMAIL_LINE_BREAK','<br>');

    /**
     * Define o endereço de e-mail da biblioteca. Esse endereço é utilizado para envio dos e-mails.
     */
     define('EMAIL_ADMIN','gnutecadevel@gmail.com');

    /**
     * Tempo de espera em segundos do servidor de email para envio da próxima mensagem. Esta opção é importante para evitar a sobrecarga do servidor de emails ou a interpretação de um possível ataque.
     */
     define('EMAIL_SERVER_DELAY','1');

    /**
     * Quantidade de colunas padrão para um campo Multiline.
     */
     define('FIELD_MULTILINE_COLS_SIZE','80');

    /**
     * Label superior da interface de sugestão de livros.
     */
     define('LABEL_PURCHASE_REQUEST','Adicionar uma nova sugestão de livro.');

    /**
     * Label superior da interface de sugestão de livros.
     */
     define('LABEL_PURCHASE_REQUEST_SEARCH','Esta é a lista de suas sugestões de livros.');

    /**
     * Define o sufixo do assunto do email de aprovação da solicitação de compra
     */
     define('EMAIL_PURCHASE_REQUEST_APROVE_SUBJECT','Aviso de aprovação de solicitação de compra');

    /**
     * Conteúdo do email de aviso de aprovação de solicitação de material. Variáveis: $username, $purchaseRequestId, $content
     */
     define('EMAIL_PURCHASE_REQUEST_INITIALIZE_CONTENT','Prezado $username $LN
    $LN
    Recebemos a solicitação de compra de número $purchaseRequestId:$LN
    $LN
    De');

    /**
     * Campos são separados por 'enter'.
    Etiqueta, rótulo,ajuda, requerido e pesquisável são separados por pipe '|'.
    Ordem dos valores: Etiqueta MARC|Rótulo do campo|Ajuda do campo|Requerido|Pesquisável
    Exemplo: 100.a|Autor|Inseir o autor da obra|t|t
     */
     define('FIELDS_PURCHASE_REQUEST','245.a|Título|Inserir o título da obra|t|t
    100.a|Autor|Inserir o autor da obra|t|t
    260.b|Editora||f|');

    /**
     * Conteúdo do email de aviso de aprovação de solicitação de material. Variáveis: $username, $purchaseRequestId, $content, $comment
     */
     define('EMAIL_PURCHASE_REQUEST_APROVE_CONTENT','Prezado $username $LN
    $LN
    A solicitação de compra de número $purchaseRequestId foi aprovada:$LN
    $LN');

    /**
     * Define o sufixo do assunto do email de cancelamento da solicitação de compra
     */
     define('EMAIL_PURCHASE_REQUEST_CANCEL_SUBJECT','Aviso de cancelamento de solicitação de compra');

    /**
     * Define o sufixo do assunto do email de inicio da solicitação de compra
     */
     define('EMAIL_PURCHASE_REQUEST_INITIALIZE_SUBJECT','Confirmação de solicitação de compra');

    /**
     * Define o modelo de etiqueta que será utilizado para imprimir os códigos de barras. O valor utilizado é o código da etiqueta.
     */
     define('DEFAULT_BARCODE_LABEL_LAYOUT','3');

    /**
     * Define se é para enviar notificações de novos materiais por e-mail.
     */
     define('USER_SEND_NOTIFY_AQUISITION','t');

    /**
     * Define o titulo de email que envia o resultado das notificações de aquisições para o administrador.
     */
     define('EMAIL_ADMIN_NOTIFY_ACQUISITION_RESULT_SUBJECT','Comunicação das notificações de aquisições');

    /**
     * Conteudo do e-mail que é enviado para o administrador com o resultado da notificação de aquisições.
     */
     define('EMAIL_ADMIN_NOTIFY_ACQUISITION_RESULT_CONTENT','Segue abaixo o resultado do comunicado de reservas atendidas.$LN $CONTENT');

    /**
     * Valor padrão que indica o intervalo para notificações das novas aquisições
     */
     define('USER_NOTIFY_AQUISITION','15');

    /**
     * Define o sufixo no assunto do e-mail que será enviado ao usuário para avisá-lo de novas aquisições. Complementa o parâmetro EMAIL_SUBJECT_PREFIX.
     */
     define('EMAIL_NOTIFY_ACQUISITION_SUBJECT','Novas aquisições');

    /**
     * Define o conteúdo do e-mail que será enviado para os usuário quando da notificação de aquisições.
    $USER_NAME - Nome do usuário
    $DATE_AQUISITIONS - Data inicial das aquisições
    $ACQUISITIONS - Aquisições da biblioteca no período
     */
     define('EMAIL_NOTIFY_ACQUISITION_CONTENT','Prezado(a) $USER_NAME $LN $LN
    Os materiais abaixo foram adquiridos desde $DATE_AQUISITIONS pela bib');

    /**
     * Define o endereço de e-mail da biblioteca. Esse endereço é utilizado para envio dos e-mails de notificação de término de requisição.
     */
     define('EMAIL_ADMIN_NOTIFY_END_REQUEST','admbiblio@univates.br');

    /**
     * Tamanho padrão para campos do tipo DATE
     */
     define('FIELD_DATE_SIZE','12');

    /**
     * Conteudo do email que sera enviado com o recibo de alteração de multa em anexo.
     */
     define('EMAIL_FINE_RECEIPT_CONTENT','Prezado(a) $USER_NAME, $LN $LN
    Uma de suas multas teve valor alterado ou foi abonada, portanto, seg');

    /**
     * Define a linguagem do conteudo.
     */
     define('EMAIL_CONTENT_TYPE','html');

    /**
     * Quantidade de linhas padrão para um campo Multiline.
     */
     define('FIELD_MULTILINE_ROWS_SIZE','10');

    /**
     * Tamanho padrão para campos com Mnemônicos
     */
     define('FIELD_MNEMONIC_SIZE','5');

    /**
     * Tamanho padrão para campos MONETARY
     */
     define('FIELD_MONETARY_SIZE','16');

    /**
     * Tamanho padrão para campos de Lookup
     */
     define('FIELD_LOOKUPFIELD_SIZE','8');

    /**
     * Tamanho padrão para campos DESCRIPTION nos lookups
     */
     define('FIELD_DESCRIPTION_LOOKUP_SIZE','20');

    /**
     * Ativa ou desativa o valor padrão de formularios.
     */
     define('FORM_CONTENT','t');

    /**
     * Número máximo de registros por página nas listagens
     */
     define('LISTING_NREGS','20');

    /**
     * Porta do servidor de impressao a ser utilizada
     */
     define('PRINT_SERVER_PORT','1515');

    /**
     * Comando que indica para impressora que o recibo deve ser cortado na impressao (separado por virgula em ASCII code)
     */
     define('PRINT_SERVER_CUT_COMMAND','27,109');

    /**
     * Conteúdo do email de aviso de cancelamento de solicitação de material.
    Variáveis: $username, $purchaseRequestId, $content, $comment, $controlNumberLink
     */
     define('EMAIL_PURCHASE_REQUEST_CANCEL_CONTENT','Prezado $username $LN
    $LN
    A solicitação de compra de número $purchaseRequestId foi cancelada:$LN
    $L');

    /**
     * Conteúdo do email de aviso de finalização de solicitação de material. Variáveis: $username, $purchaseRequestId, $content, $comment,$controlNumberLink
     */
     define('EMAIL_PURCHASE_REQUEST_FINALIZE_CONTENT','Prezado $username $LN
    $LN
    A solicitação de compra de número $purchaseRequestId foi finalizada:$LN
    O');

    /**
     * Define o endereço de email do administrador para solicitação de compras.
     */
     define('EMAIL_ADMIN_PURCHASE_REQUEST','trialforce@gmail.com');
}
?>