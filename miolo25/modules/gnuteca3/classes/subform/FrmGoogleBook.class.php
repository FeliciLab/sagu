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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 05/09/2010
 *
 **/
//Isso está aqui em funções das ações dos detalhes
$MIOLO->getClass( 'gnuteca3' , 'controls/GMaterialDetailOther');
$MIOLO->uses('classes/controls/GoogleBookViewer.class.php','gnuteca3');

class FrmGoogleBook extends GSubForm
{
    public function __construct()
    {
        $this->gridName = 'GrdGoogleBookSearch';
        $this->gridSearchMethod = 'searchGoogleBook';
        $MIOLO = MIOLO::getInstance();
        $this->business = $MIOLO->getBusiness('gnuteca3','BusGoogleBook');

        parent::__construct(_M('Google Livros', 'gnuteca3'));

        if ( GUtil::getAjaxFunction() == '' )
        {
            GForm::setFocus('query',false);
        }
    }

    /**
     * A checagem de acesso deste formulário é via integração com google
     * 
     * @return boolean
     */
    public function checkAcces()
    {
        return MUtil::getBooleanValue( GB_INTEGRATION );
    }

    /**
     * Este formulário não precisa de login do usuário
     *
     * @return boolean
     */
    public static function isUserLoginNeeded()
    {
        return false;
    }

    public function createFields()
    {
        $busSearchFormat    = $this->manager->getBusiness($this->module, 'BusSearchFormat');
        $searchFormat       = $busSearchFormat->listSearchFormat(false, !GOperator::isLogged() );

        $fields[] = new MTextField('query', null, _M('Todos os campos',$this->module), FIELD_DESCRIPTION_SIZE, "Aspas duplas podem ser utilizadas nos campos.");
        $fields[] = new MTextField('titleS', null, _M('Título',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('author', null, _M('Autor',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('publisher', null, _M('Editora',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('isbn', null, _M('Isbn',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('issn', null, _M('Issn',$this->module), FIELD_DESCRIPTION_SIZE);

        $busDomain = $this->manager->getBusiness('gnuteca3','BusDomain');
        $fields[] = new GSelection('language', null, _M('Idioma',$this->module), $busDomain->listDomain('IDIOMA_PESQUISA_GOOGLE'), false, '', '', true);
        $fields[] = new GSelection( 'searchFormat', SIMPLE_SEARCH_SEARCH_FORMAT_ID, _M('Formato de pesquisa') , $searchFormat,null,null,null,true );
        $fields[] = new MDiv( '', _M('Este serviço depende da conexão com o Google.', $this->module ) );
        $fields[] = new MDiv( '', _M('Passe o cursor sobre a imagem para ver uma descrição sobre livro.', $this->module ) );
        $fields   = GUtil::alinhaForm($fields);
        $this->setFields( $fields , true);
    }

    /**
     * Ao montar a grid bota os dados na sessão para obter os detalhes sem precisar ir no google novamente
     *
     * @return MGrid
     */
    public function getGrid()
    {
        $grid = parent::getGrid();

        //bota os dados na sessão para obter os detalhes sem precisar ir no google novamente
        $data = $grid->getData();

        //converte os GMaterialItem para stdclass para poder ir pra sessão
        if ( is_array($data) )
        {
            foreach ( $data as $line => $info )
            {
                $materialList = $info[21];

                if ( is_array( $materialList ) )
                {
                    foreach ( $materialList as $l => $gMaterialItem)
                    {
                        $materialList[$l] = $gMaterialItem->toStdClass();
                    }
                }

                $data[$line][21] = $materialList;
            }
        }

        $_SESSION['googleSearchData'] = $data;

        return $grid;
    }

    /**
     * Detalhes do material
     * @param <type> $index
     */
    public function detail($index)
    {
        //obtem dados da sessao par não precisar ir no google novamente
        $data                   = $_SESSION['googleSearchData'];
        $item                   = $data[$index];

        $embeddable             = $item[8];
        $view                   = $item[10];
        $gnutecaControlNumber   = $item[3];
        $googleControlNumber    = '';

        //ativa ou desativa a ação de mostrar o livro de acordo com a situação
        if ( $embeddable == DB_TRUE && ( $view == DB_TRUE || $view == 'p') )
        {
            $googleControlNumber = $item[2];
        }

        $materialList = $item[21];

        if ( is_array( $materialList ) )
        {
            foreach ( $materialList as $line => $info )
            {
                $materialList[$line] = GMaterialItem::fromStdClass( $info );
            }
        }

        $materialDetailOther = new GMaterialDetailOther( $materialList );
        $materialDetailOther->setGoogleControlNumber( $googleControlNumber  );
        $materialDetailOther->setControlNumber( $gnutecaControlNumber );
        GForm::injectContent( $materialDetailOther , null, _M('Detalhes', $this->module));
    }
}
?>