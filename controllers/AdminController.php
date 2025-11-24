<?php

class AdminController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // VERIFICAÇÃO DE SEGURANÇA
        // Se não estiver logado OU se o tipo não for 'admin' -> Expulsa!
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'admin') {
            // Redireciona para a home ou exibe erro
            header('Location: ' . BASE_URL . '/home');
            exit;
        }
    }

    public function dashboard() {
        $pdo = Conexao::getInstance();

        // --- INDICADOR 1: Total de Vendas (Soma do valor dos pedidos) ---
        // (Usamos COALESCE para retornar 0 se não houver vendas, em vez de NULL)
        $sqlVendas = "SELECT COALESCE(SUM(valor_total), 0) as total FROM pedidos";
        $stmt = $pdo->query($sqlVendas);
        $totalVendas = $stmt->fetch(PDO::FETCH_OBJ)->total;

        // --- INDICADOR 2: Contagem de Produtos ---
        $sqlProd = "SELECT COUNT(*) as qtd FROM produtos";
        $stmt = $pdo->query($sqlProd);
        $totalProdutos = $stmt->fetch(PDO::FETCH_OBJ)->qtd;

        // --- INDICADOR 3: Produtos com Estoque Baixo (Menos de 50 unidades) ---
        $sqlBaixo = "SELECT COUNT(*) as qtd FROM produtos WHERE estoque < 50";
        $stmt = $pdo->query($sqlBaixo);
        $stockBaixo = $stmt->fetch(PDO::FETCH_OBJ)->qtd;

        // --- INDICADOR 4: Total de Clientes ---
        $sqlClientes = "SELECT COUNT(*) as qtd FROM usuario"; // ou 'usuarios'
        $stmt = $pdo->query($sqlClientes);
        $totalClientes = $stmt->fetch(PDO::FETCH_OBJ)->qtd;

        // Renderiza a View, passando os dados
        render('admin/dashboard', [
            'titulo'        => 'Painel Administrativo',
            'vendas'        => $totalVendas,
            'produtos'      => $totalProdutos,
            'stock_baixo'   => $stockBaixo,
            'clientes'      => $totalClientes
        ]);
    }

    public function produtos() {
        $pdo = Conexao::getInstance();
        $produtoModel = new Produto($pdo);
        $lista = $produtoModel->listar();

        render('admin/produtos', [
            'titulo' => 'Gestão de Produtos',
            'produtos' => $lista
        ]);
    }

    public function usuarios() {
        $pdo = Conexao::getInstance();
        // Query simples para listar usuários
        $sql = "SELECT * FROM usuario ORDER BY id_usuario DESC";
        $stmt = $pdo->query($sql);
        $usuarios = $stmt->fetchAll(PDO::FETCH_OBJ);

        render('admin/usuarios', [
            'titulo' => 'Gestão de Usuários',
            'usuarios' => $usuarios
        ]);
    }

    public function promover($id) {
        $pdo = Conexao::getInstance();
        $sql = "UPDATE usuario SET tipo_usuario = 'admin' WHERE id_usuario = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        header('Location: ' . BASE_URL . '/admin/usuarios');
    }

    public function rebaixar($id) {
        // Impede que você se rebaixe a si mesmo (opcional, mas bom)
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