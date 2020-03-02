<?php

    class MAutoComplete extends MBusiness
    {
    
        public $module;
        public $item;
        public $value;
        public $config;
        public $sql;
        public $defaults;
        public $result;
        
        public function __construct($module,$item,$value,$defaults=null)
        {
            $this->value    = $value;
            $this->module   = $module;
            $this->item     = $item;
            $this->defaults = $defaults;
            parent::__construct($module);
            
            $MIOLO = MIOLO::getInstance();
            
            if( ( file_exists( $MIOLO->getModulePath( $module, 'db/lookup.class')) 
                    && $MIOLO->uses('/db/lookup.class',$module) )
                || $MIOLO->uses('/classes/lookup.class',$module) )
            {
                eval("\$object = new Business{$module}Lookup();");
                eval("\$info   = \$object->autoComplete{$item}(\$this,\$defaults);");
                parent::__construct($this->module);

                if($info)
                {
                    $this->result  = $info;
                }
                else
                {
                    //faz consulta
                    $sql = new Msql('');
                    $sql->createFrom($this->sql);
                    $sql->prepare($value);
                    $db = $MIOLO->getDatabase ( $this->module );
                    //$this->sql = MSql::prepare($this->sql,$value);
                    //$result = $this->_db->query($value ? $sql->command : str_replace('?','NULL',$this->sql));
                    $result = $db->query($value ? $sql->command : str_replace('?','NULL',$this->sql));
                    $this->result = $result[0];
                }
            }
        }

        public function getResult()
        {
            return $this->result;
        }
        
        public function setContext($config,$sql)
        {
            $this->config = $config;
            $this->module = $config;
            $this->sql    = $sql;
        }

    }
?>
