<?php

class MActionControl extends MInputControl
{
    public $click;
    public $action = ''; // '', <url>, <go:url>, <:action>, <window:url>, SUBMIT, PRINT, RESET, REPORT, PDF, RETURN, NONE, WINDOW

    public function __construct( $name, $value, $label = '', $color = '', $hint = '' )
    {
        parent::__construct( $name, $value, $label, $color, $hint );
    }

    public function setActionType($actionType)
    {
        $this->action = $actionType;
    }

    public function getOnClick($ref, $action, $attr)
    {
        if ($action == '')
        {
            $action = $this->action;
        }
        $upper = strtoupper($action);
        if ( substr($ref, 0,11) == 'javascript:' )
        {
            $onclick = $ref;
        }
        elseif (($ref != '') && ($action == ''))
        {
            $onclick = "javascript:miolo.doLink(this.{$attr},'{$this->formId}');";
        }
        elseif ( $upper == 'SUBMIT' )
        {

            if ( count($this->eventHandlers) )
            {
                $param   = $this->eventHandlers['click']['param'];
                $onclick = "miolo.doPostBack('{$this->name}:click','{$param}','{$this->formId}');";
            }
            else
            {
                if (( $this->name != '' ) && ($onclick == ''))
                {
                    $onclick = "miolo.doPostBack('{$this->name}:click','','{$this->formId}');";
                }
            }
            if ( $this->onclickdisable )
            {
                $onclick .= "miolo.doDisableButton('{$this->name}');";
            }
        }
        elseif ( $upper == 'PRINT' )
        {
            $onclick = "miolo.doPrintForm();";
        }
        elseif ($upper == 'REPORT')
        {
            if ( $this->name != '' )
            {
                $onclick = "miolo.doPostBack('{$this->name}:click',''); miolo.doPrintFile();";
            }
        }
        elseif ( $upper == 'PDF' )
        {
            if ( $this->name != '' )
            {
                $onclick = "miolo.doShowPDF('{$this->name}:click','','{$this->formId}');";
            }
        }
        elseif ( $upper == 'RETURN' )
        {
            global $history;
            $href = $history->back('action');
            $onclick = "miolo.doHandler('$href','{$this->formId}');";
        }
        elseif ( substr($upper, 0, 7) == 'WINDOW:' )
        {
            $url = substr($action, 7);
            $onclick = "miolo.doWindow('{$url}');";
        }
        elseif ( $upper == 'NONE' )
        {
            return "";
        }
        elseif ( preg_match('/^HTTPS{0,1}\:\/\//', $upper) )
        {
            if ( count($this->eventHandlers) )
            {
                $param   = $this->eventHandlers['click']['param'];
                $onclick = "miolo.doLinkButton('$action','{$this->name}:click','{$param}','{$this->formId}');";
            }
            elseif ( $this->name != '' )
            {
                $onclick = "miolo.doLinkButton('$action','{$this->name}:click','','{$this->formId}');";
            }
            else
            {
                $onclick = "miolo.doLinkButton('$action','','','{$this->formId}');";
            }
            $goto = $action;
        }
//        elseif ( substr($action, 0, 7) == 'HTTP://' )
//        {
//            return "miolo.doHandler('$this->action','{$this->formId}');";
//        }
        elseif ( substr($upper, 0, 3) == 'GO:' )
        {
            $goto = substr($action, 3);
            $onclick = "miolo.doHandler('$goto','{$this->formId}');";
        }
        elseif ( $action{0} == ':' )
        {
            $event = substr($action, 1);
            $onclick = $this->manager->getUI()->getAjax($event);
        }
        elseif ( substr($upper, 0,11) == 'JAVASCRIPT:' )
        {
            $onclick = $action;
        }
        else
        {
            $onclick = $action;
        }
        return $onclick . ' return false;';
    }
}
?>