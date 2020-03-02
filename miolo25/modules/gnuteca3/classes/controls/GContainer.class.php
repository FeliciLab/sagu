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
 * GContainer, created during conversion to miolo 2.5 stable
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 02/0/2010
 *
 **/
class GContainer extends MHContainer
{
    public function __construct($name = NULL, $controls = NULL)
    {
        //converte para array caso seja necessário
        if ( !is_array( $controls ))
        {
            $controls = array( $controls );
        }

        parent::__construct($name, $controls);
        $this->formMode = MFormControl::FORM_MODE_SHOW_NBSP;
    }

    /**
     * Função que aplica requirido ao label, faz acessibilidade (alt e title), dentre outros.
     *
     * @param array $controls
     * @return array
     */
    public static function parseControls($controls)
    {
        $labelToField  = '';
        
        if ( is_array($controls) )
        {
            $pos = 0; //posição no array de controls
            $find = false; //flag para se achou ou não um campo com validador

            foreach ( $controls as $i=> $control )
            {
                if ( $control instanceof MLabel )
                {
                     //tira os : que podem vir junto com a label para por como alt, tira tags, por alt e title não interpretam html
                    $labelToField = strip_tags( str_replace(':','',$control->value) );

                    //coloca ":" no final se não tiver
                    if ( substr($control->value, -1) != ':' )
                    {
                        $control->value .= ':';
                    }

                    $pos = $i;
                }
                else
                {
                    //caso não tenha encontrado uma label, tenta pegar do campo
                    if ( !$labelToField )
                    {
                        $labelToField = $control->label;
                    }

                    //acessibilidade
                    if ( !$control->getAttribute('title') )
                    {
                        $control->addAttribute('alt', $labelToField);
                        $control->addAttribute('title', $labelToField);
                    }
                }

                //procura validador do tipo requerido e verifica se campo realmente não possui label
                if ( ( $control->validator instanceof StdClass) && ( strlen($control->label) == 0 ) )
                {
                    if ( $control->validator->type == 'required' )
                    {
                        $find = true;
                        break;
                    }
                }
            }
        }

        //se achou campo requerido, coloca "*" na label
        if ( $find )
        {
            $controls[$pos]->setClass('mCaptionRequired');
        }

        return $controls;
    }

    public function generate()
    {
        //trata os campos para acessibilidade, labels e requeridos
        $this->setControls(GContainer::parseControls( $this->getControls() ) );
        return parent::generate();
    }
    
    public function setReadOnly($status) 
    {
        parent::setReadOnly($status);
        
        foreach ($this->getControls() as $control )
        {
            //Se o componente ja tiver o readonly definido anteriormente, usa o valor anterior
            if ( !$control->readonly )
            {
                $control->setReadOnly($status);
            }
        }
    }
}
?>