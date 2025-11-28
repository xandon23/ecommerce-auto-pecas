<?php

class PedidoController
{

    private $pedidoModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . BASE_URL . '/index');
            exit;
        }

        $pdo = Conexao::getInstance();
        $this->pedidoModel = new Pedido($pdo);
    }

    public function listar()
    {
        $idUsuario = $_SESSION['usuario']['id'];
        $pedidos = $this->pedidoModel->listarPorUsuario($idUsuario);

        render('pedido/listar', [
            'titulo' => 'Meus Pedidos',
            'pedidos' => $pedidos
        ]);
    }

    public function detalhes($idPedido)
    {
        $itens = $this->pedidoModel->getItensDoPedido($idPedido);

        render('pedido/detalhes', [
            'titulo' => 'Detalhes do Pedido #' . $idPedido,
            'itens' => $itens,
            'id_pedido' => $idPedido
        ]);
    }
}
