<?php

class AdminController
{

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();


        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'admin') {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }
    }

    public function dashboard()
    {
        $pdo = Conexao::getInstance();

        $sqlVendas = "SELECT COALESCE(SUM(valor_total), 0) as total FROM pedidos";
        $stmt = $pdo->query($sqlVendas);
        $totalVendas = $stmt->fetch(PDO::FETCH_OBJ)->total;

        $sqlProd = "SELECT COUNT(*) as qtd FROM produtos";
        $stmt = $pdo->query($sqlProd);
        $totalProdutos = $stmt->fetch(PDO::FETCH_OBJ)->qtd;

        $sqlBaixo = "SELECT COUNT(*) as qtd FROM produtos WHERE estoque < 50";
        $stmt = $pdo->query($sqlBaixo);
        $stockBaixo = $stmt->fetch(PDO::FETCH_OBJ)->qtd;

        $sqlClientes = "SELECT COUNT(*) as qtd FROM usuario";
        $stmt = $pdo->query($sqlClientes);
        $totalClientes = $stmt->fetch(PDO::FETCH_OBJ)->qtd;

        render('admin/dashboard', [
            'titulo'        => 'Painel Administrativo',
            'vendas'        => $totalVendas,
            'produtos'      => $totalProdutos,
            'stock_baixo'   => $stockBaixo,
            'clientes'      => $totalClientes
        ]);
    }

    public function produtos()
    {
        $pdo = Conexao::getInstance();
        $termo = $_GET['q'] ?? '';

        if (!empty($termo)) {
            $sql = "SELECT *, id_produto AS id FROM produtos 
                    WHERE nome LIKE :termo OR id_produto = :idExact
                    ORDER BY id_produto DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':termo', "%{$termo}%");
            $stmt->bindValue(':idExact', $termo);
            $stmt->execute();
        } else {
            $sql = "SELECT *, id_produto AS id FROM produtos ORDER BY id_produto DESC";
            $stmt = $pdo->query($sql);
        }

        $lista = $stmt->fetchAll(PDO::FETCH_OBJ);

        render('admin/produtos', [
            'titulo' => 'Gestão de Produtos',
            'produtos' => $lista,
            'busca' => $termo
        ]);
    }

    public function usuarios()
    {
        $pdo = Conexao::getInstance();
        $termo = $_GET['q'] ?? '';

        if (!empty($termo)) {
            $sql = "SELECT * FROM usuario 
                    WHERE nome LIKE :termo 
                       OR email LIKE :termo 
                       OR id_usuario = :idExact
                    ORDER BY id_usuario DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':termo', "%{$termo}%");
            $stmt->bindValue(':idExact', $termo);
            $stmt->execute();
        } else {
            $sql = "SELECT * FROM usuario ORDER BY id_usuario DESC";
            $stmt = $pdo->query($sql);
        }

        $usuarios = $stmt->fetchAll(PDO::FETCH_OBJ);

        render('admin/usuarios', [
            'titulo' => 'Gestão de Usuários',
            'usuarios' => $usuarios,
            'busca' => $termo
        ]);
    }

    public function promover($id)
    {
        $pdo = Conexao::getInstance();
        $sql = "UPDATE usuario SET tipo_usuario = 'admin' WHERE id_usuario = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        header('Location: ' . BASE_URL . '/admin/usuarios');
    }

    public function rebaixar($id)
    {
        if ($id == $_SESSION['usuario']['id']) {
            echo "<script>alert('Você não pode remover seu próprio acesso!'); history.back();</script>";
            exit;
        }

        $pdo = Conexao::getInstance();
        $sql = "UPDATE usuario SET tipo_usuario = 'cliente' WHERE id_usuario = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        header('Location: ' . BASE_URL . '/admin/usuarios');
    }
}
