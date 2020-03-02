<?php

//
// defines user access right constants, which are used in the
// $NIOLO->checkAccess() and ThemeMenu->addUserOption() methods.
//

define('A_ACCESS',    1); // 000001
define('A_QUERY',     1); // 000001

define('A_INSERT',    2); // 000010
define('A_DELETE',    4); // 000100
define('A_UPDATE',    8); // 001000
define('A_EXECUTE',  15); // 001111

define('A_SYSTEM',   31); // 011111
define('A_ADMIN',    31); // 011111

define('A_DEVELOP',  32); // 100000

?>
