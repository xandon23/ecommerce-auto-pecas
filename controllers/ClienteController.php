<?php

class ClienteController {

    private $enderecoModel;
    private $usuarioModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Segurança: Só logado entra
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . BASE_URL . '/index');
            exit;
        }

        $pdo = Conexao::getInstance();
        $this->enderecoModel = new Endereco($pdo);
        $this->usuarioModel = new Usuario($pdo);
    }

    // Painel Principal do Cliente
    public function index() {
        $meusEnderecos = $this->enderecoModel->listarPorUsuario($_SESSION['usuario']['id']);
        
        render('cliente/index', [
            'titulo' => 'Minha Conta',
            'enderecos' => $meusEnderecos
        ]);
    }

    // Salvar Endereço
    public function salvarEndereco() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST['id_usuario'] = $_SESSION['usuario']['id'];
            
            $this->enderecoModel->salvar($_POST);
            echo "<script>alert('Endereço salvo!'); location.href='".BASE_URL."/cliente';</script>";
        }
    }

    // Excluir Endereço
    public function excluirEndereco($id) {
        $this->enderecoModel->excluir($id, $_SESSION['usuario']['id']);
        header('Location: ' . BASE_URL . '/cliente');
    }

    // Alterar Senha
    public function alterarSenha() {
        $novaSenha = $_POST['nova_senha'] ?? '';
        $idUsuario = $_SESSION['usuario']['id'];

        if (!empty($novaSenha)) {
            // Criptografa
            $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
            
            // Atualiza no banco (precisa adicionar método no Usuario.php ou fazer query direta)
            $pdo = Conexao::getInstance();
            $sql = "UPDATE usuario SET senha = :senha WHERE id_usuario = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':senha', $hash);
            $stmt->bindValue(':id', $idUsuario);
            $stmt->execute();

            echo "<script>alert('Senha alterada com sucesso!'); location.href='".BASE_URL."/cliente';</script>";
        } else {
            echo "<script>alert('Digite uma senha válida.'); history.back();</script>";
        }
    }
}