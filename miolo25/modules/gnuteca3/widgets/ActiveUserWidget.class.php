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
 * Class
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 **/
class ActiveUserWidget extends GWidget
{
    /** @var BusinessGnuteca3BusAnalytics */
    public $business;

    public function __construct()
    {
        $this->module   = 'gnuteca3';
        parent::__construct('activeUserWidget', _M('Usuários ativos', 'gnuteca3'), "",'user-16x16.png' );
    }

    public function widget()
    {
        $this->business = $this->manager->getBusiness($this->module, 'BusAnalytics');

        $operators = $this->business->listActiveOperators();
        
        if ( is_array( $operators ) && $operators[0][0] )
        {
            //obtém o nome dos operadores
            foreach( $operators as $l => $operator )
            {
                $operators[$l][0] = GOperator::getOperatorName($operator[0]);
            }
            
            $controls[] = new MTableRaw( null, $operators, array( _M('Operadores', $this->module) ), 'tblOperators');
        }
        else
        {
            $controls[] = new MLabel( _M( 'Sem operadores ativos', $this->module) );
        }

        $persons = $this->business->listActivePersons();
        if ( is_array( $persons ) && $persons[0][0]  )
        {
            $controls[] = new MTableRaw( null, $persons, array( _M('Pessoa', $this->module),  _M('Nome', $this->module)  ), 'tblPerson');
        }
        else
        {
            $controls[] = new MLabel( _M( 'Sem pessoas ativas', $this->module) );
        }
        
        $this->setControls($controls);
    }
}
?>