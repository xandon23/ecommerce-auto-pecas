<?php

class IndexController
{

    private $usuario;
    private $pedido;

    public function __construct()
    {
        $pdo = Conexao::getInstance();

        $this->usuario = new Usuario($pdo);
        $this->pedido = new Pedido($pdo);
    }

    public function index()
    {
        if (isset($_SESSION['usuario'])) {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }
        render('login/login', ['titulo' => 'Login']);
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

        if ($dadosUsuario && password_verify($senha, $dadosUsuario->senha)) {

            $_SESSION["usuario"] = array(
                "id" => $dadosUsuario->id_usuario,
                "nome" => $dadosUsuario->nome,
                "tipo" => $dadosUsuario->tipo_usuario
            );

            $carrinhoSessao = $_SESSION['carrinho'] ?? [];

            $carrinhoBanco = $this->pedido->recuperarCarrinho($dadosUsuario->id_usuario);

            if (!empty($carrinhoBanco)) {
                foreach ($carrinhoBanco as $idProd => $itemBanco) {
                    if (isset($carrinhoSessao[$idProd])) {
                        $carrinhoSessao[$idProd]['qtd'] += $itemBanco['qtd'];
                    } else {
                        $carrinhoSessao[$idProd] = $itemBanco;
                    }
                }
            }

            $_SESSION['carrinho'] = $carrinhoSessao;

            header('Location: ' . BASE_URL . '/home');
            exit;
        } else {
            echo "<script>alert('Dados inválidos'); location.href='" . BASE_URL . "/index';</script>";
        }
    }

    public function cadastro()
    {
        render('login/cadastro', ['titulo' => 'Criar Conta']);
    }

    public function registrar()
    {
        $nome           = $_POST['nome'] ?? null;
        $email          = $_POST['email'] ?? null;
        $senha          = $_POST['senha'] ?? null;
        $confirmarSenha = $_POST['confirmar_senha'] ?? null;

        if ($nome && $email && $senha && $confirmarSenha) {

            if ($senha !== $confirmarSenha) {
                echo "<script>alert('As senhas não coincidem. Tente novamente.'); history.back();</script>";
                exit;
            }

            if ($this->usuario->getEmailUsuario($email)) {
                echo "<script>alert('Este email já está cadastrado!'); location.href='" . BASE_URL . "/index/cadastro';</script>";
                exit;
            }

            $this->usuario->salvar([
                'nome' => $nome,
                'email' => $email,
                'senha' => $senha
            ]);

            echo "<script>alert('Conta criada com sucesso! Faça login.'); location.href='" . BASE_URL . "/index';</script>";
        } else {
            echo "<script>alert('Preencha todos os campos'); history.back();</script>";
        }
    }

    public function sair()
    {
        if (isset($_SESSION['usuario']) && isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
            $this->pedido->salvarCarrinho($_SESSION['usuario']['id'], $_SESSION['carrinho']);
        }

        session_destroy();

        header('Location: ' . BASE_URL . '/home');
        exit;
    }
}
