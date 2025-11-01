<?php
    class Usuario {

        private $pdo;

        public function __construct($pdo)
        {
            $this->pdo = $pdo;
        }

        public function getEmailUsuario($email) {
            $sql = "select id_usuario, nome, email, senha from usuario where ativo = 'S' and email = :email 
                limit 1";
            $consulta = $this->pdo->prepare($sql);
            $consulta->bindParam(":email", $email);
            $consulta->execute();

            return $consulta->fetch(PDO::FETCH_OBJ);
        }
    }