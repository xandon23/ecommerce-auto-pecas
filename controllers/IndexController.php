<?php

class IndexController {
    
    private $usuario;

    public function __construct()
    {
        $pdo = Conexao::getInstance();
        
        $this->usuario = new Usuario($pdo);
    }

    public function index()
    {
        render('login', ['titulo' => 'Login']); 
    }

    public function verificar() 
    {
        $email = $_POST["email"] ?? NULL;
        $senha = $_POST["senha"] ?? NULL;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>mensagem('Digite um e-mail válido','error','');</script>";
            exit;
        } else if (empty($senha)) {
            echo "<script>mensagem('Senha inválida','error','');</script>";
            exit;
        }

        $dadosUsuario = $this->usuario->getEmailUsuario($email); 

        if (empty($dadosUsuario->id_usuario)) { 
            echo "<script>mensagem('Usuário inválido','error','');</script>";
            exit;
        } else if (!password_verify($senha, $dadosUsuario->senha)) {
            echo "<script>mensagem('Senha inválida','error','');</script>";
            exit;
        } else {
            session_start(); 
            $_SESSION["usuario"] = array(
                "id" => $dadosUsuario->id_usuario,
                "nome" => $dadosUsuario->nome
            );
            echo "<script>location.href='home'</script>"; 
        }
    }
}