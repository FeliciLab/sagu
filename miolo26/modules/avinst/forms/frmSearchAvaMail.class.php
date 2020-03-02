<?php

/**
 * Formulário de busca da tabela ava_mail.
 *
 * @author Name [name@solis.coop.br]
 *
 * \b Maintainers: \n
 * Name [name@solis.coop.br]
 *
 * @since
 * Creation date 25/01/2012
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class frmSearchAvaMail extends ASearchForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaMail';
        parent::__construct(_M('Envio de emails', MIOLO::getCurrentModule()));
    }

    /**
     * Criar os campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('types/avaPerfil.class.php',$module);
        $MIOLO->uses('types/avaPerfil.class.php',$module);
        $perfil = new avaPerfil();
        $perfil->__set('avaliavel',DB_TRUE);
        $avaliacao = new avaAvaliacao();        
        $fields[] = new MTextField('idMail', '', _M('Código', $module), 10);
        $fields[] = new MSelection('refAvaliacao', null, _M('Avaliação', $module),  $avaliacao->getAvaliacoesAbertas());
        $fields[] = new MSelection('refPerfil', null, _M('Perfil', $module), $perfil->search());
        $fields[] = new MSelection('tipoEnvio', '', _M('Envio', $module), avaMail::getSendTypes() );
        $fields[] = new MSelection('grupoEnvio', '', _M('Enviar para', $module), avaMail::getSendGroups() );
        $fields[] = $this->getButtons();
        $MIOLO = MIOLO::getInstance();
        $this->grid = $this->manager->getUI()->getGrid($module, 'grdSearchAvaMail');
        $this->grid->setData($this->searchButton_click((Object)array('return'=>true)));
        $fields[] = new MDiv('divGrid', $this->grid->generate());
        $this->addFields($fields);
    }
}


?>