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
    // Alterar Senha (Com validação completa)
    public function alterarSenha() {
        $senhaAtual     = $_POST['senha_atual'] ?? '';
        $novaSenha      = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        $idUsuario      = $_SESSION['usuario']['id'];

        // 1. Verifica se preencheu tudo
        if (empty($senhaAtual) || empty($novaSenha) || empty($confirmarSenha)) {
            echo "<script>alert('Preencha todos os campos.'); history.back();</script>";
            exit;
        }

        // 2. Verifica se a nova senha e a confirmação são iguais
        if ($novaSenha !== $confirmarSenha) {
            echo "<script>alert('A nova senha e a confirmação não coincidem.'); history.back();</script>";
            exit;
        }

        // 3. Busca a senha criptografada (hash) atual no banco para conferir
        $pdo = Conexao::getInstance();
        $sql = "SELECT senha FROM usuario WHERE id_usuario = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $idUsuario);
        $stmt->execute();
        $usuarioBanco = $stmt->fetch(PDO::FETCH_OBJ);

        // 4. Verifica se a 'Senha Atual' digitada bate com a do banco
        if (!password_verify($senhaAtual, $usuarioBanco->senha)) {
            echo "<script>alert('A senha atual está incorreta.'); history.back();</script>";
            exit;
        }

        // 5. Tudo certo! Criptografa a NOVA senha e salva
        $hashNova = password_hash($novaSenha, PASSWORD_DEFAULT);
        
        $sqlUpdate = "UPDATE usuario SET senha = :senha WHERE id_usuario = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindValue(':senha', $hashNova);
        $stmtUpdate->bindValue(':id', $idUsuario);
        $stmtUpdate->execute();

        echo "<script>alert('Senha alterada com sucesso!'); location.href='".BASE_URL."/cliente';</script>";
    }
}