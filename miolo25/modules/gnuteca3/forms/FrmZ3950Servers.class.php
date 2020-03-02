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
 * Cadastro de servidores Z39.50
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 29/12/2010
 *
 **/
class FrmZ3950Servers extends GForm
{
    public function __construct()
    {
        $this->module = MIOLO::getCurrentModule();
        $this->setAllFunctions('Z3950Servers', null, 'serverId', 'description');
        parent::__construct();
    }

    public function mainFields()
    {
        if ($this->function != 'insert')
        {
            $fields[] = new MTextField('serverId', '', _M('Código',$this->module), FIELD_ID_SIZE,null, null, true);
        }

        $fields[] = new MTextField('description', '', _M('Descrição',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('host', '', _M('Endereço',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('recordType', '', _M('Tipo', $this->module), BusinessGnuteca3BusDomain::listForSelect('Z3950_RECORD_TYPE'));
        $fields[] = new MTextField('sintax', '', _M('Sintaxe',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('country', '', _M('País',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('username', '', _M('Usuário',$this->module), 20);
        $fields[] = new MTextField('password', '', _M('Senha',$this->module), 20);

        //validadores
        $validators[] = new MRequiredValidator('description');
        $validators[] = new MRequiredValidator('host');
        $validators[] = new MRequiredValidator('recordType');

        $this->setFields($fields);
        $this->setValidators($validators);
    }
}
?>