<?php
    require "../config/Conexao.php";
    require "../models/Usuario.php";

    class IndexController {

        private $usuario;

        public function __construct()
        {
            $pdo = Conexao::conectar();

            $this->usuario = new Usuario($pdo);
        }

        public function verificar($dados) {

            $email = $dados["email"] ?? NULL;
            $senha = $dados["senha"] ?? NULL;

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<script>mensagem('Digite um e-mail válido','error','')</script>";
                exit;
            } else if (empty($senha)) {
                echo "<script>mensagem('Senha inválida','error','')</script>";
                exit;
            }

            $dadosUsuario = $this->usuario->getEmailUsuario($email);

            if (empty($dadosUsuario->id)) {
                echo "<script>mensagem('Usuário inválido','error','')</script>";
                exit;
            } else if (!password_verify($senha, $dadosUsuario->senha)) {
                echo "<script>mensagem('Senha inválida','error','')</script>";
                exit;
            } else {
                $_SESSION["usuario"] = array(
                    "id" => $dadosUsuario->id,
                    "nome" => $dadosUsuario->nome
                );
                echo "<script>location.href='index.php'</script>";
            }


        }

    }