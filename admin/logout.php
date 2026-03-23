<?php
session_start();
session_unset();
session_destroy();

header('Location: /pruebas%20burijazz/Festival_Burijazz/admin/login.php');
exit;