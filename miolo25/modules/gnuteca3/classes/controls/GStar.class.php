<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GStar,class
 *
 * @author trialforce
 */
class GStar extends MDiv
{
    /**
     * Quantidade de estrelas a montar
     */
    const STAR_QUANTITY = 5;
    const STAR_DEFAULT_IMAGE_SIZE = 32;

    /**
     * O Input que guarda o valor
     * @var MTextField
     */
    protected $input;
    /**
     * Tamanho da imagem
     * 
     * @var integer 
     */
    protected $imageSize = 32;

    /**
     * Controí o componente de estrelas
     * 
     * @param string $name não é possível utilizar '_'.
     * @param integer $value
     * @param booleam $readOnly
     */
    public function __construct($name = null, $value = '', $readOnly = false, $imageSize = 32 )
    {
        //foi concatenado o '_' para o id do campo venha corretamente no post
        parent::__construct($name.'_', '', 'gStar' );
        $this->setReadOnly( $readOnly );
        $this->input = new MTextField( $name  );
        $this->input->addStyle('display','none');
        $this->setValue( $value );
        $this->setImageSize( $imageSize );
    }

    public function setValue( $value )
    {
        $this->input->value = $value;
    }

    public function getValue( )
    {
        return $this->input->value;
    }

    /**
     * Define o tamanho da imagem
     *
     * @param integer $size
     */
    public function setImageSize( $size )
    {
        $this->imageSize = intval( $size );
    }

    /**
     * Retorna o tamanho da imagem
     *
     * @return int
     */
    public function getImageSize()
    {
        return intval( $this->imageSize ) ;
    }

    public function generate()
    {
        $imgStar = GUtil::getImageTheme('star.png');
        $imgStarDisable = GUtil::getImageTheme('star_disabled.png');
        $starCount = GStar::STAR_QUANTITY;

        $value = $this->getValue();

        for ( $i = 1 ; $i <= $starCount ; $i++ )
        {
            $selectedImage = $value >= $i ? $imgStar : $imgStarDisable;

            if ( $this->readonly )
            {
                $inner[] = $image = new MImage( $this->name.'star'.$i, '', $selectedImage );

            }
            else
            {
                $inner[] = $image = new MImageLink( $this->name.'star'.$i, '', "javascript:gnuteca.setStar('{$this->name}','{$i}', '{$starCount}' );", $selectedImage );
                $image->image->id = $this->name.'star'.$i.'img';
            }

            if ( $this->imageSize != GStar::STAR_DEFAULT_IMAGE_SIZE )
            {
                $image->addStyle('height', $this->imageSize.'px' );
                $image->addStyle('width', $this->imageSize.'px' );
            }
        }

        $inner[] = $this->input;

        $this->setInner( $inner );
        $this->addStyle('width', ( GStar::STAR_QUANTITY  * ( $this->imageSize +4 ) ). 'px !important');

        return parent::generate();
    }

    /**
     * Define por javascript o valor da estrela
     *
     * @param string $starName nome do objeto da estrela (id)
     * @param integer $value normalmente de 0 a 5
     */
    public static function jsSetValue( $starName, $value )
    {
        $MIOLO = MIOLO::getInstance();
        $starCount = GStar::STAR_QUANTITY;
        $js = "gnuteca.setStar('{$starName}_','{$value}', '{$starCount}' );";
        $MIOLO->page->onload( $js );
    }
}
?>
