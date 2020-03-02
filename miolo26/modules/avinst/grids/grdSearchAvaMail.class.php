<?php
$MIOLO->uses('types/avaAvaliacao.class.php', MIOLO::getCurrentModule());
$MIOLO->uses('types/avaPerfil.class.php', MIOLO::getCurrentModule());

/**
 * Grid da tabela ava_mail.
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 24/01/2012
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
class grdSearchAvaMail extends MGrid
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        
        $avaliacao = new avaAvaliacao();
        foreach ($avaliacao->search() as $row)
        {
            $avaliacoes[$row[0]] = $row[1];
        }
        
        $perfil = new avaPerfil();
        foreach ($perfil->search() as $row)
        {
            $perfis[$row[0]] = $row[1];
        }
        
        $MIOLO->uses('types/avaFormulario.class.php',$module);
        $formulario = new avaFormulario();
        $formularios[null] = _M('Todos');
        foreach ( $formulario->search() as $row )
        {
            $formularios[$row[0]] = $row[3];
        }
        
        $columns[] = new MGridColumn(_M('Código', $module), 'right', true, '1%', true, NULL, true);
        $columns[] = new MGridColumn(_M('Avaliação', $module), 'left', true, '10%', true, $avaliacoes, true);
        $columns[] = new MGridColumn(_M('Perfil', $module), 'left', true, '10%', true, $perfis, true);
        $columns[] = new MGridColumn(_M('Formulario', $module), 'left', true, '10%', true, $formularios, true);
        $columns[] = new MGridColumn(_M('Horário', $module), 'left', true, '1%', true, NULL, true);
        $columns[] = new MGridColumn(_M('Assunto', $module), 'left', true, '50%', true, NULL, true);
        $columns[] = new MGridColumn(_M('Conteudo', $module), 'left', true, '50%', false, NULL, true);
        $columns[] = new MGridColumn(_M('Envio', $module), 'left', true, '1%', true, avaMail::getSendTypes(), true);
        $columns[] = new MGridColumn(_M('Enviar para', $module), 'left', true, '1%', true, avaMail::getSendGroups(), true);
        $columns[] = new MGridColumn(_M('Estado', $module), 'center', true, '1%', true, null, true);
        $primaryKeys = array('idMail'=>'%0%', );
        $url = $MIOLO->getActionUrl($module, $action);
        parent::__construct(NULL, $columns, $url);
        $args = array(MUtil::getDefaultEvent()=>'editButton:click', 'function'=>'edit' );
        $hrefUpdate = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $args = array(MUtil::getDefaultEvent()=>'deleteButton:click', 'function'=>'search' );
        $hrefDelete = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $this->addActionUpdate($hrefUpdate);
        $this->addActionDelete($hrefDelete);
        $this->setRowMethod($this, 'myRowMethod');        
    }
    
    public function myRowMethod($i, $row, $actions, $columns)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaMail.class.php','avinst');
        $data->idMail = $row[0];
        $avaMail = new avaMail($data,true);
        $totalEnviados = $avaMail->obterTotalEnviados();
        $totalNaoEnviados = $avaMail->obterTotalNaoEnviados();
        $actions[0]->disable(); // desabilita a ação editar
        $actions[1]->disable(); // desabilita a ação excluir               
        
        if( $avaMail->processoRodando() )
        {
            $image = new MImage(null,null,$MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'email_processing.gif'));
            $estado = $totalEnviados == 0 ? 'preparando para enviar' : "$totalEnviados de ".($totalEnviados+$totalNaoEnviados).' enviados';
            $msg = new MLabel($image->generate()." Processando: $estado ",'darkorange',true);
        }
        else
        {
            if( ($totalEnviados > 0 && $totalEnviados == ($totalEnviados+$totalNaoEnviados)) || strlen($avaMail->__get('processo')) > 0 )
            {
                $msg = new MLabel("Finalizado: ".($totalEnviados+$totalNaoEnviados).' enviados','green',true);                
            }
            else
            {
                $actions[0]->enable(); // Ação editar so fica liberada no estado aguardando
                $actions[1]->enable(); // Ação excluir so fica liberada no estado aguardando
                $msg = new MLabel('Aguardando ...','red',true);
            }            
        }
        
        $columns[9]->control[$i] = $msg;        
    }
}


?>
