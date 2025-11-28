<?php

class ClienteController
{

    private $enderecoModel;
    private $usuarioModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . BASE_URL . '/index');
            exit;
        }

        $pdo = Conexao::getInstance();
        $this->enderecoModel = new Endereco($pdo);
        $this->usuarioModel = new Usuario($pdo);
    }

    public function index()
    {
        $meusEnderecos = $this->enderecoModel->listarPorUsuario($_SESSION['usuario']['id']);

        render('cliente/index', [
            'titulo' => 'Minha Conta',
            'enderecos' => $meusEnderecos
        ]);
    }

    public function salvarEndereco()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST['id_usuario'] = $_SESSION['usuario']['id'];

            $this->enderecoModel->salvar($_POST);
            echo "<script>alert('Endereço salvo!'); location.href='" . BASE_URL . "/cliente';</script>";
        }
    }

    public function excluirEndereco($id)
    {
        $this->enderecoModel->excluir($id, $_SESSION['usuario']['id']);
        header('Location: ' . BASE_URL . '/cliente');
    }

    public function alterarSenha()
    {
        $senhaAtual     = $_POST['senha_atual'] ?? '';
        $novaSenha      = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        $idUsuario      = $_SESSION['usuario']['id'];

        if (empty($senhaAtual) || empty($novaSenha) || empty($confirmarSenha)) {
            echo "<script>alert('Preencha todos os campos.'); history.back();</script>";
            exit;
        }

        if ($novaSenha !== $confirmarSenha) {
            echo "<script>alert('A nova senha e a confirmação não coincidem.'); history.back();</script>";
            exit;
        }

        $pdo = Conexao::getInstance();
        $sql = "SELECT senha FROM usuario WHERE id_usuario = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $idUsuario);
        $stmt->execute();
        $usuarioBanco = $stmt->fetch(PDO::FETCH_OBJ);

        if (!password_verify($senhaAtual, $usuarioBanco->senha)) {
            echo "<script>alert('A senha atual está incorreta.'); history.back();</script>";
            exit;
        }

        $hashNova = password_hash($novaSenha, PASSWORD_DEFAULT);

        $sqlUpdate = "UPDATE usuario SET senha = :senha WHERE id_usuario = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindValue(':senha', $hashNova);
        $stmtUpdate->bindValue(':id', $idUsuario);
        $stmtUpdate->execute();

        echo "<script>alert('Senha alterada com sucesso!'); location.href='" . BASE_URL . "/cliente';</script>";
    }
}
