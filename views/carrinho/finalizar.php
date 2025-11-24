<?php

public function finalizar() {
    $itensCarrinho = $_SESSION['carrinho'] ?? [];

    // se o carrinho estiver vazio, volta pra página do carrinho
    if (empty($itensCarrinho)) {
        header('Location: ' . BASE_URL . '/?url=carrinho');
        exit;
    }

    // aqui você poderia calcular total, etc.
    $total = 0;
    foreach ($itensCarrinho as $item) {
        $total += $item['preco'] * $item['qtd'];
    }

    render('carrinho/finalizar', [
        'titulo' => 'Finalizar Pedido',
        'itens'  => $itensCarrinho,
        'total'  => $total
    ]);
}

