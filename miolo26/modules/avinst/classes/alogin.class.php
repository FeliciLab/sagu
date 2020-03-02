<?php

class ALogin extends MLogin
{
    private $perfis;
    
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $id (tipo) desc
     * @param $password (tipo) desc
     * @param $user (tipo) desc
     * @param $idkey (tipo) desc
     * @param $setor' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function __construct($user='', $password='', $name='', $idusuario='', $setor = '')
    {
        parent::__construct($user, $password, $name, $idusuario, $setor);
    }
    
    public function setPerfis($perfis)
    {
        $this->perfis = $perfis;
    }
    
    public function getPerfis()
    {
        return $this->perfis;
    }
}
?>
