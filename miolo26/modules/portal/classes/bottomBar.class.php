<?php

/**
 * Description of bottomBar
 *
 * @author jonas
 */
class bottomBar extends MDiv
{
    public $buttons;

    public function __construct()
    {
        parent::__construct();

        $this->setId("controleCabecalho");
        $this->addAttribute('data-content-theme', 'd');
        $this->addStyle('bottom', '0');
        $this->addStyle('position', 'fixed');
        $this->addStyle('width', '100%');
        $this->addStyle('margin', '0');
        $this->addStyle('padding', '0');
        $this->addStyle('z-index', '100');
        
        if(MUtil::getBrowser()=='Firefox')
        {
            $this->addStyle('background', '-moz-linear-gradient(#268CEB, #1F72BF) repeat scroll 0 0 #1F72BF;');
        }
        else
        {
            $this->addStyle('background', '-webkit-gradient(linear, left top, left bottom,from(#268CEB), to(#1F72BF)) repeat scroll 0 0 #1F72BF;');
        }
        
        $this->addStyle('border', '1px solid #456F9A');

        $this->buttons = $buttons;
    }

    public function addButton($name, $action, $icon)
    {
        $this->buttons[$name] = array('name'=>$name, 'action'=>$action, 'icon'=>$icon);
    }

    public function generateInner()
    {
        $applyCss = count($this->buttons) > 2;
        if ( $applyCss )
        {
            $letras = array('a', 'b', 'c', 'd');
            $i = 0;
            $toolbar = '<ul class="ui-grid-c">';
        }
        else
        {
            $toolbar = '<ul>';
        }
        
	foreach($this->buttons as $button)
	{
            $action = $button['action'];
            
            if(MUtil::getBrowser()=='Firefox')
            {
                if(substr($action,0,11) == 'javascript:')
                {
                    $onclick = 'onclick = "'.substr($action,11,strlen($action)).';"';
                    $action = '#';
                }
            }
            
            if ( $applyCss )
            {
                $letra = $letras[$i];
                $i++;
                
                // Por causa do problema com o MChart da tela de estatísticas, não faz ajax.
                if ( MIOLO::_REQUEST('action') == 'main:estatisticaDisciplina' && $button['name'] == 'Voltar' )
                {
                    $onclick = '';
                }
                
                $toolbar .= '<li class="ui-block-' . $letra . '">
                                <a href="'.$action.'" '.$onclick.' data-corners="false" data-shadow="false" data-iconshadow="true" data-wrapperels="span" data-theme="c" data-inline="true" class="ui-btn ui-btn-inline ui-btn-up-c">
                                    <span class="ui-btn-inner"><span class="ui-btn-text">
                                        <div><img src="'.$button['icon'].'" width="64" height="64" /></div>
                                        <div>'.$button['name'].'</div>
                                    </span></span>
                                </a>
                             </li>';
            }
            else
            {
                $toolbar .= '<li>
                                <a href="'.$action.'" '.$onclick.'>
                                        <div><img src="'.$button['icon'].'" width="64" height="64" /></div>
                                        <div>'.$button['name'].'</div>
                                </a>
                             </li>';
            }                        
	}
	
        $imgRodaPe = SAGU::getParameter('PORTAL', 'IMAGEM_RODA_PE_DO_PORTAL');
        
	$toolbar .= '</ul>'; 
         
        if ( strlen($imgRodaPe) > 0 )
        {
            $toolbar .= "<img src='{$imgRodaPe}' style='position: fixed; bottom:5px; right:5px; height: 100px;' />";
        }
	
        
	$div = new MDiv('',$toolbar);
	$div->addAttribute('data-role', 'navbar');
        if ( $applyCss )
        {
            $div->setClass('ui-navbar ui-navbar-noicons');
            $div->addAttribute('role', 'navigation');
        }
        $div->addStyle('background', '-moz-linear-gradient(#268CEB, #1F72BF) repeat scroll 0 0 #1F72BF;');

	$div2 = new MDiv('',$div);
	$div2->addAttribute('data-role', 'footer');
	$div2->addStyle('width', '50%');

        $this->setInner('<center>'.$div2->generate().'</center>');
        return parent::generateInner();
    }
}

?>
