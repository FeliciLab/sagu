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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 11/06/2012
 *
 **/
class bCSVColumn
{
    const TYPE_STRING = 1;
    const TYPE_INT = 2;
    const TYPE_DATE = 3;
    const TYPE_DOUBLE = 4;
    const TYPE_BOOLEAN = 5;
    const TYPE_SEX = 6;
    const TYPE_YEAR = 7;
    const TYPE_CHAR = 8;
    const TYPE_ESTADO = 9;
    
    // Campos tipos booleanos
    public static $booleanRangesAll = array('t', 's', 'sim',
                                            'f', 'n', 'nao');
    public static $booleanRangesTrue = array('t', 's', 'sim');
    public static $booleanRangesFalse = array('f', 'n', 'nao');
    
    // Campos tipos sexo
    public static $sexRanges = array('F','M');
    
    /**
     * Nome da coluna no arquivo CSV
     * 
     * @var string
     */
    private $name;
    
    /**
     * Define um label para exibicao amigavel nas mensagens desta coluna
     *
     * @var string 
     */
    private $label;
    
    /**
     * Indica se coluna e requerida no arquivo CSV
     *
     * @var boolean 
     */
    private $isRequired = false;
    
    /**
     * Indica se deve ser unico
     *
     * @var boolean
     */
    private $isUnique = false;
    
    /**
     * Tipo de dado da coluna
     *
     * @var string
     */
    private $type;
    
    /**
     * Array com valores que devem ser substituidos
     *
     * @var array 
     */
    private $replaceVars = array();
    
    /**
     * Nome da tabela e coluna na base de dados (exemplo: Basphysicalperson.name)
     * 
     * @var string
     */
    private $databaseColumn;
    
    /**
     * Define o limite minimo de caracteres
     *
     * @var int
     */
    private $minLength = 0;
    
    /**
     * Define o limite maximo de caracteres
     *
     * @var int
     */
    private $maxLength = 1000;
    
    /**
     * Restringe para que este campo no CSV tenha apenas estes valores.
     * Exemplo: array('M', 'F') - valida para que o campo no CSV possa ter apenas estes valores.
     *
     * @var string
     */
    private $restrictValues = array();
    
    /**
     * Range inicial do intervalo
     *
     * @var int
     */
    private $rangeStart = null;
    
    /**
     * Range final do intervalo
     *
     * @var int
     */
    private $rangeEnd = null;
    
    
    /**
     * Posicao desta coluna no array bCSVFileImporter
     *
     * @var int
     */
    private $colPosition = null;
    
    
    public function __construct()
    {
        $this->setType(self::TYPE_STRING);
    }
    
    /**
     * Verifica se os dados passados sao suficientes para ler arquivo CSV
     *
     * @throws Exception 
     */
    public function _validateParams()
    {
        if ( strlen($this->name) <= 0 )
        {
            throw new Exception( _M('Não foi definido um nome para a coluna.') );
        }
        
        if ( strlen($this->label) <= 0 )
        {
            throw new Exception( _M('Não foi definido um label para a coluna.') );
        }
    }
    
    
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getIsRequired()
    {
        return $this->isRequired;
    }

    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;
    }

    public function getIsUnique()
    {
        return $this->isUnique;
    }

    public function setIsUnique($isUnique)
    {
        $this->isUnique = $isUnique;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        if ( strlen($type) > 0 )
        {
            // Restringe valores booleanos
            if ( $type == self::TYPE_BOOLEAN )
            {
                $this->setRestrictValues(self::$booleanRangesAll);
            }
            else if ( $type == self::TYPE_SEX )
            {
                $this->setRestrictValues(self::$sexRanges);
            }
            else if ( $type == self::TYPE_YEAR )
            {
                $this->setMinAndMaxLength(4);
                $this->setRanges(1000, 5000);
            }
            else if ( $type == self::TYPE_CHAR )
            {
                $this->setMinAndMaxLength(1);
            }
            else if ( $type == self::TYPE_ESTADO )
            {
                $this->setMinAndMaxLength(2);
                $this->setRestrictValues(array('AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'));
            }

            $this->type = $type;
        }
    }


    public function getDatabaseColumn()
    {
        return $this->databaseColumn;
    }

    public function setDatabaseColumn($databaseColumn)
    {
        $this->databaseColumn = $databaseColumn;
    }

    public function getMinLength()
    {
        return $this->minLength;
    }

    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;
    }

    public function getMaxLength()
    {
        return $this->maxLength;
    }

    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
    }
    
    public function setMinAndMaxLength($minMaxLength)
    {
        $this->minLength = $minMaxLength;
        $this->maxLength = $minMaxLength;
    }
    
    public function getRestrictValues()
    {
        return $this->restrictValues;
    }

    public function setRestrictValues($restrictValues)
    {
        $this->restrictValues = $restrictValues;
    }
    
    public function getRangeStart()
    {
        return $this->rangeStart;
    }

    public function getRangeEnd()
    {
        return $this->rangeEnd;
    }

    /**
     * Define o limite de intervalo permitido para o valor do campo
     * 
     * @param int $rangeStart
     * @param int $rangeEnd
     */
    public function setRanges($rangeStart, $rangeEnd)
    {
        $this->rangeStart = $rangeStart;
        $this->rangeEnd = $rangeEnd;
    }
        
    public function getReplaceVars()
    {
        return $this->replaceVars;
    }

    public function setReplaceVars($replaceVars)
    {
        if ( $replaceVars )
        {
            $this->replaceVars = $replaceVars;
        }
    }
    
    public function getColPosition()
    {
        return $this->colPosition;
    }

    public function setColPosition($colPosition)
    {
        $this->colPosition = $colPosition;
    }
        
    /**
     * Obtem expressoes SQL de validadores
     * 
     * @return array
     */
    public function getValidateExpressions()
    {
        $cases = array();
        
        $colName = $this->getName();
        $colLabel = '"' . $this->getLabel() . '"';
        $minLength = $this->getMinLength();
        $maxLength = $this->getMaxLength();

        // Validacao de requerido
        if ( $this->getIsRequired() )
        {
            $cases[] = "(CASE WHEN {$colName} = '' OR {$colName} IS NULL THEN 'O campo {$colLabel} e requerido.\n' ELSE '' END)";
        }

        // Validacao de limite de caracteres
        $cases[] = "(CASE WHEN ( CHAR_LENGTH({$colName}) > 0 ) AND ( CHAR_LENGTH({$colName}) NOT BETWEEN {$minLength} AND {$maxLength} ) THEN 'O campo {$colLabel} deve possuir entre {$minLength} e {$maxLength} caracteres.\n' ELSE '' END)";

        // Validacao de valores restritos
        if ( count($this->restrictValues) > 0 )
        {
            $restrict = SAGU::quoteArrayStrings($this->restrictValues, null, 'strtolower');
            $restrict = implode(',', $restrict);
            $restrictDisplay = str_replace("'", "\"", $restrict);
            $cases[] = "(CASE WHEN CHAR_LENGTH({$colName}) > 0 AND (lower({$colName}) NOT IN ({$restrict})) THEN 'O campo {$colLabel} esta deve possuir apenas um dos seguintes valores: {$restrictDisplay}.\n' ELSE '' END)";
        }
        
        // Validacao de intervalos (ranges)
        if ( strlen($this->rangeStart) > 0 && strlen($this->rangeEnd) > 0 )
        {
            $cases[] = "( CASE WHEN CHAR_LENGTH({$colName}) > 0 AND ({$colName}::int NOT BETWEEN {$this->rangeStart}::int AND {$this->rangeEnd}::int) THEN 'O campo {$colLabel} deve estar no intervalo entre {$this->rangeStart} e {$this->rangeEnd}.\n' ELSE '' END)";
        }

        switch ( $this->getType() )
        {
            // Validacao de integer
            case bCSVColumn::TYPE_INT:
                $cases[] = "(CASE WHEN ( CHAR_LENGTH({$colName}) > 0 ) AND ( NOT({$colName} ~ '^([0-9]+)$') ) THEN 'O campo {$colLabel} deve ser numerico.\n' ELSE '' END)";
                break;

            // Validacao de datas

            // Validacao de CPF
        }
        
        return $cases;
    }
}
?>
