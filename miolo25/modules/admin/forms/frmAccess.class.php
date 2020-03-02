<?php

class frmAccess extends MForm
{
    public function __construct( $login )
    {
        parent::__construct( _M('Access') );

        $MIOLO = $this->manager;
        $user  = $MIOLO->getBusiness( 'admin', 'user' );
        $ui    = $MIOLO->getUI();

        $user->getByLogin( $login->id );

        if ( $lastAccess = $login->lastAccess )
        {
           $msgAccess = "Seu Ãºltimo acesso ocorreu em $lastAccess[0] Ã s $lastAccess[1] ($lastAccess[2]).";
        }

        $img    = new MImageFormLabel( 'imgLogo', '', $ui->getImage( '', 'logo_miolo_new.png' ) );

        $fields = array( new MLabel( 'Welcome, ' . $user->nickname ),
                         new MLabel( $msgAccess ),
                         new MLabel( '<b>ATEN&Ccedil;&Atilde;O</b>: N&atilde;o deixe de encerrar sua sess&atilde;o (clicando em Sair) antes de fechar a janela do seu browser.' ),
                         $img
                        );

        $this->setFields( $fields );
        $this->defaultButton = false;
    }
}

?>