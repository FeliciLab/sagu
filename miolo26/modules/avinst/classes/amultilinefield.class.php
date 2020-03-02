<?php

/**
 * Caixa de texto em tooltip
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/12/15
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2008 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

class AMultiLineField extends MSpan
{
    function __construct( $id , $options, $value = null)
    {
        
        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->addDojoRequire( 'dijit.form.DropDownButton' );
        $MIOLO->page->addDojoRequire( 'dijit.Tooltip' );
        $MIOLO->page->addDojoRequire( 'dijit.TooltipDialog' );
        $MIOLO->page->addDojoRequire( 'dijit.Dialog' );
        
        $fieldsMultiLine[] = new MSpan(null, 'Comentar');
        $multiLine[] = new MMultiLineField($id.'TextArea', $value, null, $options->size, $options->height, $options->size);
        $multiLine[0]->setClass('avinstTextArea');
        $multiLine[0]->addAttribute('onKeyUp',"dojo.byId('$id').value = this.value;");
        //$multiLine[0]->addAttribute('onFocus',"dijit.byId('aMultiLineField_$id').hide();");
        if (strlen($options->charLimit)>0)
        {
            $multiLine[0]->addAttribute('maxlength', $options->charLimit);
        }
        $multiLine[] = new MSpan("{$id}_Close",'OK');
        $multiLine[1]->addAttribute('dojoType', 'dijit.form.Button');
        $multiLine[1]->addAttribute('type', 'submit');
        $multiLine[1]->setClass('aMultiLineFieldCloseButton');        
        $fieldsMultiLine['div'] = new Div(null, $multiLine);
        $fieldsMultiLine['div']->addAttribute('dojoType', 'dijit.TooltipDialog');
        $fieldsMultiLine['div']->addAttribute('align', 'center');
        $fieldsMultiLine['div']->addStyle('height', '100%');        
        $fields['divMultiLine'] = new MDiv('aMultiLineField_value_'.$id, $fieldsMultiLine);
        $fields['divMultiLine']->addAttribute('dojoType', 'dijit.form.DropDownButton');
        $fields['divMultiLine']->setClass('aMultiLineField');
        $fields[] = new MHiddenField($id,$value);                
        parent::__construct( 'aMultiLineField_'.$id, $fields);     
    }
}
?>
