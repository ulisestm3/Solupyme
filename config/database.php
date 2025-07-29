<?php
function getConnection(): mysqli
{
    $host     = 'localhost';
    $user     = 'root';
    $password = '';
    $dbname   = 'awferreteria';

    $mysqli = new mysqli($host, $user, $password, $dbname);

    if ($mysqli->connect_errno) {
        error_log("Error de conexión: " . $mysqli->connect_error);
        throw new Exception("Error de conexión a la base de datos.");
    }
    

    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}