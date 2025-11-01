<?php

    class Conexao {

        private static $host = "localhost";
        private static $user = "root";
        private static $db = "ecommerce_projeto";
        private static $pass = "";

        public static function conectar() {
            try {
                return new PDO("mysql:host=".self::$host.";
                            dbname=".self::$db.";
                            charset=utf8",
                            self::$user,
                            self::$pass);

            } catch(PDOException $e) {
                die("<p>Erro ao conectar no banco: {$e->getMessage()}</p>");
            }
        }

    }