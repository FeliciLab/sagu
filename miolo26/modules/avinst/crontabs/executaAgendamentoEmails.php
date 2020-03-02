<?php 

/*
 *	Script que é chamado pela crontab para iniciar o processo que envia emails
 *  em background para os agendamentos de emails. 
 * 
 */

// Instancia o MIOLO na variável $MIOLO
include_once 'miolo25.php';
$module = MIOLO::getCurrentModule();
$MIOLO->uses('types/avaMail.class.php',$module);

// Pega o periodo dentro da hora atual
$horarioInicial = date('d/m/Y G:00');
$horarioFinal = date('d/m/Y G:i', strtotime(date('m/d/Y G:00')." + 1 hour"));
 
// Busca os emails que devem ser enviados
$avaMail = new avaMail();
$avaMail->__set('tipoEnvio',avaMail::TIPO_ENVIO_AGENDADO); // Se tipo de envio for agendado
$avaMail->__set('horarioInicial',$horarioInicial); // Pega os agendamentos que não foram executados ainda
$avaMail->__set('horarioFinal',$horarioFinal); // Pega os agendamentos que não foram executados ainda
$agendamentos = $avaMail->search(ADatabase::RETURN_TYPE);

if( ! empty($agendamentos) ) // Se existir pelo menos uma agendamento
{
    foreach ($agendamentos as $agendamento)
    {
        if( strlen($agendamento->__get('processo')) == 0 ) // Pega apenas os agendamentos que nunca tiveram um processo rodando
        {
            $idMail = $agendamento->__get('idMail');
            // Dispara o processo de evio de e-mail em background http://www.ptpatv.info/index.php?db=so&id=45953
    		$pid = exec("php {$MIOLO->getModulePath($module,'crontabs')}/emails.php $idMail > /tmp/amail.log 2>&1 & echo $!");
    		// Atualiza pid do lote de emails com o processo que esta rodando em background
    		$sql = 'UPDATE ava_mail SET processo = ? WHERE id_mail = ?';
    		$result = ADatabase::execute($sql, array($pid,$idMail));
        }
    }
}
?>