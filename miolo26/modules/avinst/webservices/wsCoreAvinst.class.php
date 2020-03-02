<?php

class TSoapClient extends SoapClient
{
    public function __construct($location = null)
    {
        $MIOLO = MIOLO::getInstance();
        $location     = strlen($location)>0 ? $location : $MIOLO->getConf('wsCore.location');
        $info = pathinfo($location);
        $uri	      = $info['dirname'];
        $trace	      = $MIOLO->getConf('wsCore.trace');
        $encoding     = $MIOLO->getConf('wsCore.encoding');
        $this->key    = $MIOLO->getConf('wsCore.key');
        parent::__construct(null, array('location'=>"$location",'uri'=>"$uri",'trace'=>$trace,'encoding'=>"$encoding"));
    }


    public function __call($name, $arguments)
    {
        $arguments[]= $this->key;
        $result = parent::__soapCall($name, $arguments);
        $result = unserialize(base64_decode($result));
        if ( $result instanceof Exception )
        {
            throw $result;
        }
        else
        {
            return $result;
        }
    }
}

class wsCoreAvinst
{   
    //
    // Chama o serviço get perfis pessoa
    //
    // TODO: Deixar essa chamada configurável
    public function getVinculosPessoa($refPessoa)
    { 
        try
        {
            $wsCore = new TSoapClient();
            $perfis = $wsCore->executaMetodoModel('Basico::Vinculos', 'getPerfisPessoa', array('$login'=>$refPessoa));
            return $perfis;
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }
    
    //
    // Chama o serviço
    //
    public function chamaServico($localizacao, $metodo, $parametros, $cache = true)
    {
        $parametros = is_array($parametros) ? $parametros : array();
        $MIOLO = MIOLO::getInstance();
        if ($localizacao == 'avinstServicoInterno')
        {
            $MIOLO->uses('classes/ainternalservices.class.php', 'avinst');
            if (in_array($metodo, get_class_methods('AInternalServices')))
            {
                $retorno = AInternalServices::$metodo($parametros);
            }
            else
            {
                return false;
            }
        }
        else
        {
            $sessString = $localizacao.'_'.$metodo.'_'.implode('_',$parametros);
            if ($cache == true)
            {
                $retorno = $MIOLO->getSession()->getValue($sessString);
            }
            if ((is_null($retorno)) || ($retorno == false))
            {
                $wsCore = new TSoapClient($localizacao);
                
                // Se o método for quebrado em 3 partes, executa via MetodoModel (ALFA)
                $metodoPartes = explode('::', $metodo);
                if (count($metodoPartes) == 3)
                {
                    $retorno = $wsCore->executaMetodoModel($metodoPartes[0].'::'.$metodoPartes[1], $metodoPartes[2], $parametros);
                    mb_convert_variables(strlen($MIOLO->getConf('wsCore.encoding'))>0 ? $MIOLO->getConf('wsCore.encoding') : 'UTF-8', 'ISO-8859-1', $retorno);
                    $MIOLO->getSession()->setValue($sessString, $retorno);
                }
            }
        }
        return $retorno;
    }
}
?>
