<?php

class PedidoController {

    private $pedidoModel;

    public function __construct() {
        // 1. Segurança: Só logado pode ver pedidos
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . BASE_URL . '/index');
            exit;
        }

        $pdo = Conexao::getInstance();
        $this->pedidoModel = new Pedido($pdo);
    }

    // Lista todos os pedidos (Meus Pedidos)
    public function listar() {
        $idUsuario = $_SESSION['usuario']['id'];
        $pedidos = $this->pedidoModel->listarPorUsuario($idUsuario);

        render('pedido/listar', [
            'titulo' => 'Meus Pedidos',
            'pedidos' => $pedidos
        ]);
    }

    // Ver detalhes de um pedido específico
    // URL: /pedido/detalhes/5
    public function detalhes($idPedido) {
        // Busca itens
        $itens = $this->pedidoModel->getItensDoPedido($idPedido);
        
        render('pedido/detalhes', [
            'titulo' => 'Detalhes do Pedido #' . $idPedido,
            'itens' => $itens,
            'id_pedido' => $idPedido
        ]);
    }
}