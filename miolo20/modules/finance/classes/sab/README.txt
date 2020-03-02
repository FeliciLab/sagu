Manual de configuração de boletos

Passo para configuração de boletos
1.0 - Configuração do banco padrão
    1.1 - Alteração de um banco padão
    1.2 - Cadastro de um banco padrão
2.0 - Configuração do SAB
    2.1 - Alteração do SAB
    2.2 - Cadastro do SAB

1 - Cadastrar no módulo básico um banco padrão para as faturas. Abaixo segue um exemplo de configuração.

    Vá até a tabela de parâmetros do módulo basico conforme caminho abaixo
    Home::Básico::Configuração::Tabela de parâmetros

    No campo parâmetro digite "DEFAULT_INVOICE_BANK" (sem aspas) depois precione o botão de localizas.
    Em caso de haver um banco cadastrado, o mesmo pode ser alterado comforme a necessidade do usuário (passo 1.1). Caso o banco que é utilizado pela instituição esteje cadastrado corretamente vá para o passo 2.0.
    1.1 - Alteração de um banco padão

    Para editar clique no botão de AÇÂO "edit" que está representado por uma figura de um caderno com lápis.

    Altere o campo valor VALOR para o banco que a instituição utiliza.
    Após alterado o código do banco clique no botão de salvar representado pela figura de um disquete.
    OBS: O valor deve ser numérico com no máximo 3 caracteres. Ex: 104 ou 001 ...
    
    1.2 - Cadastro de um banco padrão

    Para cadastrar um novo banco padrão devemos ir no mesmo caminho do passo 1 e clicar no botão de novo.
    
    No campo MÓDULO selecione: BASIC
    No campo PARÂMATRO digite: "DEFAULT_INVOICE_BANK" (sem aspas)
    No campo VALOR digite o código do banco utilizado pela instituição: Ex: 104
    No campo ASSINATURA sugerimos: "Número do banco padrão de geração de títulos" (sem aspas)
    No campo DESCRIÇÃO sugerimos: "Número do banco padrão de geração de títulos" (sem aspas)
    No campo TIPO DE CAMPO selecione: INTEGER
    No campo VALOR PODE SER ALTERADO selecione: SIM
    
    Após o preenchimento dos campos clique no bitão de salvar representado pela figura de um disquete.
    OBS: Só pode haver um parâmetro DEFAULT_INVOICE_BANK.
    
2 - Cadastrar no módulo básico o SAB. Abaixo segue um exemplo de configuração.

    Vá até a tabela de parâmetros do módulo basico conforme caminho abaixo
    Home::Básico::Configuração::Tabela de parâmetros

    No campo parâmetro digite "SAB_DIRECTORY" (sem aspas) depois precione o botão de localizas.
    Em caso de haver SAB cadastrado, o mesmo pode ser alterado comforme a necessidade do usuário (passo 2.1). Caso o SAB que é utilizado pela instituição esteje cadastrado corretamente vá para o passo 3.0.

    2.1 - Alteração do SAB

    Para editar clique no botão de AÇÂO "edit" que está representado por uma figura de um caderno com lápis.

    Altere o campo VALOR para o caminho onde o SAB está localizado. Ex: /usr/local/sagu/module/finance/clases/sab
    Após alterado o caminho do SAB clique no botão de salvar representado pela figura de um disquete.
    
    2.2 - Cadastro do SAB

    Para cadastrar um novo banco padrão devemos ir no mesmo caminho do passo 2 e clicar no botão de novo.
    
    No campo MÓDULO selecione: BASIC
    No campo PARÂMATRO digite: "SAB_DIRECTORY" (sem aspas)
    No campo VALOR digite a localização do SAB: Ex: /usr/local/sagu/modules/finance/classes/sab/
    No campo ASSINATURA sugerimos: "Caminho absoluto para sistema de boletos" (sem aspas)
    No campo DESCRIÇÃO sugerimos: "Caminho absoluto para diretório do sistema de boletos" (sem aspas)
    No campo TIPO DE CAMPO selecione: VARCHAR
    No campo VALOR PODE SER ALTERADO selecione: SIM
    
    Após o preenchimento dos campos clique no botão de salvar representado pela figura de um disquete.
    OBS: Só pode haver um parâmetro SAB_DIRECTORY.
