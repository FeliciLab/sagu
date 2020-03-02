<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class NavigationBar extends MMenu
{
   const SEPARATOR = '::';
   const HOME = 'Homes';

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
   public function generateInner()
   {    
       if ( $this->base )
       {
          $base = $this->base;
       }
       else
       {
          $base = $this->manager->dispatch;
       }
       $this->setCssClassItem('link', 'topMenuLink');
       $this->setCssClassItem('option', 'topMenuLink');

       $ul = new MUnorderedList();
       $options = $this->getOptions();
       if ( $count = count($options) )
       {
            $url = $this->manager->getActionURL($this->home,'main','','',$base);
            $ul->addOption( MHtmlPainter::anchor('topMenuLink', self::HOME, $url) );
            $ul->addOption(self::SEPARATOR);
            foreach ( $options as $o )
            {
                if ( --$count )
                {
                   $ul->addOption($o->generate());
                   $ul->addOption(self::SEPARATOR);
                }
                else
                {
                   $ul->addOption(MHtmlPainter::span('topMenuCurrent','',$o->control->label));
                }
            }
       }
       else // root item
       {
          $ul->addOption($this->caption);
       }
       $this->setBoxClass('topMenuBox');
       $this->inner = MHtmlPainter::generateToString($ul);
   }
}
?>
