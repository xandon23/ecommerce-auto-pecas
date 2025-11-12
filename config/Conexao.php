<?php
class Conexao {
    private static $host = "localhost";
    private static $user = "root";
    private static $db   = "ecommerce_projeto";
    private static $pass = "";
    private static $pdo  = null; // conexão única (singleton)

    public static function getInstance() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=utf8",
                    self::$user,
                    self::$pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
                    ]
                );
            } catch (PDOException $e) {
                die("<p>Erro ao conectar no banco: {$e->getMessage()}</p>");
            }
        }
        return self::$pdo;
    }
}
