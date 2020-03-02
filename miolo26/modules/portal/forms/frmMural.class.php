<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2013/01/24
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */
$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('types/PrtMensagemMural.class.php', $module);
$MIOLO->uses('types/PrtUsuarioSagu.class.php', $module);
$MIOLO->uses('classes/prtDisciplinas.class.php', $module);

class frmMural extends frmMobile
{

    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Mural', MIOLO::getCurrentModule()));
    }

    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $fields[] = $this->postagens();

        parent::addFields($fields);
    }

    public function postagens()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busFile = $MIOLO->getBusiness('basic', 'BusFile');

        $prtMensagemMural = new PrtMensagemMural();
        $prtMensagemMural->unitid = $this->unitid;
        $prtMensagemMural->personid = $this->personid;

        $postagens = $prtMensagemMural->obterMuralDoAluno();

        foreach ( $postagens as $mensagem )
        {
            $prtDisciplinas = new PrtDisciplinas();
            $nomeDisciplina = $prtDisciplinas->obterNomeDisciplina($mensagem[4]);

            $prtPessoa = new PrtUsuarioSagu($mensagem[2]);
            $professor = $prtPessoa->obterPessoa($mensagem[2]);

            $label = new MLabel($mensagem[0] . ' por ' . $professor->name . ' [ ' . $nomeDisciplina . ' ]');
            $label->addStyle('font-size', '12px');
            $label->addStyle('font-weight', 'bold');
            $label->setWidth('100%');

            $text = new MText(rand(), $mensagem[1]);
            $text->addStyle('font-size', '16px');
            $text->addStyle('margin-left', '18px');
            $text->setWidth('100%');

            $anexoDiv = NULL;
            if ( $mensagem[3] )
            {
                $file = $busFile->getFile($mensagem[3]);

                $name = basename($file->uploadFileName);
                $link = $MIOLO->getConf('home.url') . "/download.php?filename={$file->absolutePath}&contenttype={$file->contentType}&name={$name}";
                $anexoLink = new MText('lnk_' . $mensagem[3], '<a href="' . $link . '" target="_blank">' . $name . '</a>');
                $anexoDiv = new MDiv('', array( $anexoLink ));
                $anexoDiv->addStyle('font-size', '12px');
                $anexoDiv->addStyle('margin-top', '10px');
                $anexoDiv->addStyle('margin-left', '18px');
            }

            $fields[] = $cont = new MVContainer('dscsdc', array( $label, $text, $anexoDiv, $btnExcluir ));
            $cont->addStyle('padding', '10px');
            $cont->addStyle('border-style', 'solid');
            $cont->addStyle('border-width', '1px');
            $cont->addStyle('border-color', '#CCC');
        }

        $div = new MDiv('divMural', array( new MBaseGroup('baseMural', _M('Mural de postagens dos professores'), $fields, 'vertical') ));

        return $div;
    }
}

?>
