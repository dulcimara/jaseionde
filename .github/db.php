<?php
// Configurações do Banco de Dados (Locaweb fornecerá esses dados)
$host = 'jaseionde.mysql.dbaas.com.br'; 
$db   = 'jaseionde'; // Nome do seu banco
$user = 'jaseionde';          // Seu usuário do banco
$pass = 'Secret@123';              // Sua senha do banco
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Iniciar sessão para login
session_start();
?>