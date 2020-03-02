<?php

/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * @author Nataniel I. da Silva [nataniel@solis.com.br]
 *
 * @version $Id$
 *
 * @since
 * Class created on 05/09/2014
 * */

$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/filaDeEspera.class.php', $module);

class frmFilaDeEspera extends frmMobile
{
    
    public function __construct()
    {
        parent::__construct(_M('Fila de espera', MIOLO::getCurrentModule()));
    }

    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentAction();
        
        $basFilaDeEspera = new BasFilaDeEspera();
                
        $fila = $basFilaDeEspera->obtemFilaDeEspera(false);
        
        $login = $MIOLO->getLogin();
        $loginUsuario = $login->id;
        
        $posicao = null;
        $filaDeEspera = array();
        
        foreach ( $fila as $key => $usuario )
        {
            if ( $usuario[1] == $loginUsuario )
            {
                $posicao = $key+1;
            }
        }
        
        $msg = _M('No momento você está na fila para utilizar o portal. ', $module);
        $msg .= _M("Sua posição na fila é a {$posicao}°, aguarde a liberação.", $module);
        
        $msgLabel = new MLabel($msg);
        $msgLabel->setWidth(500);
        $fields[] = new MHContainer('hctMsg', array($msgLabel));
        
        $contadorLabel = new MText('contadorLabel', _M('A fila será verificada novamente em', $module) . ':');
        $contador = new MText('contador', '00:00:20');
        $fields[] = new MHContainer('hctContador', array($contadorLabel, $contador));
                        
        $campos[] = $div = new MDiv('', $fields);
        $div->addStyle('width', '500px');
        
        $dialog = new MDialog('dialogInfo', _M('Fila de espera', $module), $campos);
        $dialog->show();
                
        $jsCode = " setInterval(escondeBotao, 200);

                    function escondeBotao() {
                    var elemento = document.getElementsByClassName('dijitDialogCloseIcon');
                        elemento[0].style.display = 'none'; }";        
        
        //$jsCode .= " setTimeout(function() { window.location.reload(true); }, 20000); "; // 20000 milisegundo = 20 segundos
        
        $jsCode .= " var tempo = new Number(); 
                         tempo = 20; //em segundos
                                
                    function startCountdown()
                    {
                        // Se o tempo não for zerado
                        if( (tempo - 1) >= 0 )
                        {
                            // Pega a parte inteira dos minutos
                            var min = parseInt(tempo/60);

                            // Calcula os segundos restantes
                            var seg = tempo%60;

                            // Formata o número menor que dez, ex: 08, 07, ...
                            if(min < 10)
                            {
                                min = '0'+min;
                                min = min.substr(0, 2);
                            }

                            if(seg <=9)
                            {
                                seg = '0'+seg;
                            }

                            // Cria a variável para formatar no estilo hora/cronômetro
                            horaImprimivel = '00:' + min + ':' + seg;

                            var cont = document.getElementById('contador');
                            if ( cont != null )
                            {
                                cont.innerHTML = horaImprimivel;
                            }

                            // Define que a função será executada novamente em 1000ms = 1 segundo
                            setTimeout('startCountdown()',1000);

                            // diminui o tempo
                            tempo--;
                        }
                        else
                        {
                            document.getElementById('contador').innerHTML = '00:00:00';
                            window.location.reload(true);
                        }
                    } 
                    startCountdown(); "; 
        
        $this->page->addJsCode($jsCode);
    }
}
