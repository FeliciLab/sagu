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
class NewsWidget extends GWidget
{
    public $module;
    public $busNews;

    public function __construct()
    {
        $this->module   = 'gnuteca3';
        parent::__construct('newsWidget', _M('Notícias', $this->module), "",'news-16x16.png' );
    }

    public function widget()
    {
        $this->busNews  = $this->manager->getBusiness($this->module, 'BusNews');
        $this->busNews->libraryUnitIdS = GOperator::getLibraryUnitLogged();
        $this->busNews->librayUnitNull = true;
        $search = $this->busNews->getActiveByPlace( BusinessGnuteca3BusNews::PLACE_TYPE_INITIAL_SCREEN );

        if ($search)
        {
            foreach ($search as $v)
            {
                $news[] = '<b>' . GDate::construct($v->date)->getDate(GDate::MASK_DATE_USER) . ' :: ' . $v->title1 . '</b><br>' . str_replace("\n", '<br>', $v->news);
            }
            
            $tb = new MTableRaw(null, $news);
            $tb->setAlternate(true);

            $controls[] = $tb;
        }
        else
        {
            $controls[] = new MLabel(_M('Nenhuma notícia encontrada', $this->module));
        }
        $this->setControls($controls);
    }
}
?>