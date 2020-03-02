<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MBasePainter
{
    private static $count;
    // ----------------------------------------------------------------------
    // Generate methods 
    // ----------------------------------------------------------------------

    #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    # A helper function, which generates an array of theme elements.
    # This function is called recursively, when an array elements
    # contains another array.
    #----------------------------------------------------------------------
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $elements (tipo) desc
     * @param $separator' (tipo) desc
     * @param $method='Generate' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function generateElements($elements, $separator = '', $method = 'Generate')
    {
        if (is_array($elements))
        {
            foreach ($elements as $e)
            {
                $this->generateElements($e, $separator);
            }
        }
        else if (is_object($elements))
        {
            if (method_exists($elements, 'generate'))
            {
                if ($html = $elements->$method())
                {
                    $this->generateElements($html, $separator);
                }
                else
                    echo $separator;
            }
            else
            {
                echo "BasePainter Error: Method Generate not defined to " . get_class($elements);
            }
        }
        else if ($elements)
        {
            echo $elements . $separator;
        }
    }

    #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    # Convenience function to capture the output of an element's 
    # <code>Generate</code> function as HTML string.
    #----------------------------------------------------------------------
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $element (tipo) desc
     * @param $separator' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function generateToString($element, $separator = '')
    {
        /*
        echo 'a'; echo '#'.gettype($element).'#';
                ob_start();
        echo 'a'; echo '{'.gettype($element).'}';
                $this->generateElements($element, $separator);
                $html = ob_get_contents();
                ob_end_clean();
                return $html;
        */
        $MIOLO = MIOLO::getInstance();
        if (is_array($element))
        {
            foreach ($element as $e)
            {
                $html .= $this->generateToString($e, $separator);
            }
        }
        elseif (is_object($element))
        {
            if ( method_exists($element, 'generate') )
            {
                if ( MUtil::getBooleanValue( $MIOLO->getConf('options.loading.show') ) &&
                     MUtil::getBooleanValue( $MIOLO->getConf('options.loading.generating') ) )
                {
                    if ( $element->name )
                    {
                        MIOLO::updateLoading("Generating... {$element->name}");
                    }
                }

                $html = $element->generate() . $separator;
            }
            else
            {
                $html = "BasePainter Error: Method Generate not defined to " . get_class($elements);
            }
        }
        else
        {
            $html = (string)$element;
        }

        return $html;
    }
} // end of BasePainter
?>