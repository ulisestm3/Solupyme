<?php
// database.php

// Cambia esta variable según el entorno: 'dev' para desarrollo, 'prod' para producción
$ENV = 'prod'; // Cambia a 'prod' cuando subas al servidor

function getConnection(): mysqli
{
    global $ENV;

    if ($ENV === 'prod') {
        $host     = 'db5018358324.hosting-data.io';
        $user     = 'dbu3893836';
        $password = 'StarOne2025$.';
        $dbname   = 'dbs14534446';
    } else {
        // Entorno de desarrollo
        $host     = 'localhost';
        $user     = 'root';
        $password = '';
        $dbname   = 'awferreteria';
    }

    $mysqli = new mysqli($host, $user, $password, $dbname);

    if ($mysqli->connect_errno) {
        error_log("Error de conexión: " . $mysqli->connect_error);
        throw new Exception("Error de conexión a la base de datos.");
    }

    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}
