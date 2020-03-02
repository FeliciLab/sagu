<?php 

class AInternalServices
{
    public static function checkIsFuncionario($parametros)
    {
        $isFuncionario = in_array($parametros[0], $parametros[1]);
        return $isFuncionario;
    }

    public static function saguAutenticaPessoa($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguAutenticaPessoa($parametros);
    }
    
    public static function saguAutenticaAluno($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguAutenticaAluno($parametros);
    }
    
    public static function saguObtemCursosAluno($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguObtemCursosAluno($parametros);
    }
    
    public static function saguObtemDisciplinasAluno($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguObtemDisciplinasAluno($parametros);
    }
    
    public static function saguObtemDisciplinasAlunoPeriodoAnterior($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguObtemDisciplinasAlunoPeriodoAnterior($parametros);
    }
    
    public static function saguObtemAluno($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguObtemAluno($parametros);
    }
    
    public static function saguObtemPessoa($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguObtemPessoa($parametros);
    } 
    
    public static function saguObtemProfessor($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguObtemProfessor($parametros);
    }
    
    public static function saguObtemCoodernador($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguObtemCoodernador($parametros);
    }
    
    public static function saguObtemFuncionario($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguObtemFuncionario($parametros);
    }
    
    public static function saguAutenticaProfessor($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguAutenticaProfessor($parametros);
    }

    public static function saguAutenticaCoordenador($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguAutenticaCoordenador($parametros);
    }
    
    public static function saguObtemDisciplinasProfessor($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguObtemDisciplinasProfessor($parametros);
    }

    public static function saguObtemCursosProfessor($parametros)
    {
	$MIOLO = MIOLO::getInstance();
	$MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguObtemCursosProfessor($parametros);
    }
    
    public static function saguObtemCursosCoodernador($parametros)
    {
	$MIOLO = MIOLO::getInstance();
	$MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguObtemCursosCoodernador($parametros);
    }
    
    //
    //
    //
    public static function saguAutenticaFuncionario($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguAutenticaFuncionario($parametros);
    }
    
    //
    //
    //
    public static function saguObtemSetorFuncionario($parametros)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/asagu/asagu.class.php', 'avinst');
        $saguConn = new ASagu();
        return $saguConn->saguObtemSetorFuncionario($parametros);
    }

    //
    //
    //
    public static function saguObtemPerfis($parametros)
    {
	$MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/amanagelogin.class.php', 'avinst');
        $manageLogin = new AManageLogin();
        $manageLogin->getLoginProfiles();
    }
}
?>
