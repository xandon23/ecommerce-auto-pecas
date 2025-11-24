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
        // Verifica se já está logado
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

            // 1. Pega o carrinho que o usuário montou AGORA (como visitante)
            $carrinhoSessao = $_SESSION['carrinho'] ?? [];

            // 2. Pega o carrinho ANTIGO que estava salvo no banco
            $carrinhoBanco = $this->pedido->recuperarCarrinho($dadosUsuario->id_usuario);
            
            // 3. Faz a fusão
            if (!empty($carrinhoBanco)) {
                foreach ($carrinhoBanco as $idProd => $itemBanco) {
                    // Se o produto já existe na sessão (visitante)
                    if (isset($carrinhoSessao[$idProd])) {
                        // Soma as quantidades! (Ex: Tinha 2 no banco, pus +1 agora = 3)
                        $carrinhoSessao[$idProd]['qtd'] += $itemBanco['qtd'];
                    } else {
                        // Se não existe na sessão, adiciona o do banco
                        $carrinhoSessao[$idProd] = $itemBanco;
                    }
                }
            }

            // 4. Atualiza a sessão com o carrinho unificado
            $_SESSION['carrinho'] = $carrinhoSessao;

            header('Location: ' . BASE_URL . '/home');
            exit;
        } else {
            // Erro simples (idealmente usaria uma mensagem na view)
            echo "<script>alert('Dados inválidos'); location.href='" . BASE_URL . "/index';</script>";
        }
    }

    public function cadastro()
    {
        render('login/cadastro', ['titulo' => 'Criar Conta']);
    }

    // 2. Recebe os dados e salva
    public function registrar()
    {
        $nome = $_POST['nome'] ?? null;
        $email = $_POST['email'] ?? null;
        $senha = $_POST['senha'] ?? null;

        if ($nome && $email && $senha) {
            // Verifica se o email já existe
            if ($this->usuario->getEmailUsuario($email)) {
                echo "<script>alert('Este email já está cadastrado!'); location.href='" . BASE_URL . "/index/cadastro';</script>";
                exit;
            }

            // Salva no banco
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

    // 3. Logout
    public function sair() {
       if (isset($_SESSION['usuario']) && isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
            // Salva no banco
            $this->pedido->salvarCarrinho($_SESSION['usuario']['id'], $_SESSION['carrinho']);
        }

        session_destroy();
        
        header('Location: ' . BASE_URL . '/home');
        exit;
    }
}
