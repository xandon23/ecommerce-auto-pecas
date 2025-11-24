<?php
class CarrinhoController
{

    public function adicionar($slug)
    {
        $pdo = Conexao::getInstance();
        $produtoModel = new Produto($pdo);

        $produto = $produtoModel->getDado($slug); // slug = id

        if (!$produto) {
            header("Location: " . BASE_URL . "/home");
            exit;
        }

        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }

        if (isset($_SESSION['carrinho'][$produto->id])) {
            $_SESSION['carrinho'][$produto->id]['qtd']++;
        } else {
            $imgPath = BASE_PATH . "/public/img/produtos/{$produto->id}.jpg";
            $imgWeb  = BASE_URL  . "/img/produtos/{$produto->id}.jpg";

            if (!file_exists($imgPath)) {
                $imgWeb = BASE_URL . "/img/placeholder.jpg";
            }

            $_SESSION['carrinho'][$produto->id] = [
                'id'    => $produto->id,
                'nome'  => $produto->nome,
                'preco' => $produto->preco,
                'img'   => $imgWeb,
                'qtd'   => 1
            ];
        }

        header("Location: " . BASE_URL . "/carrinho");
    }

    public function index()
    {
        $itens = $_SESSION['carrinho'] ?? [];
        render('carrinho/index', compact('itens'));
    }

    public function remover($id)
    {
        if (isset($_SESSION['carrinho'][$id])) {
            unset($_SESSION['carrinho'][$id]);
        }
        header("Location: " . BASE_URL . "/carrinho");
    }

    public function finalizar() {
        // 1. Segurança: Só pode finalizar se estiver logado
        if (!isset($_SESSION['usuario'])) {
            // Manda para o login com aviso
            echo "<script>alert('Faça login para finalizar a compra.'); location.href='".BASE_URL."/index';</script>";
            exit;
        }

        // 2. Pega os itens
        $itensCarrinho = $_SESSION['carrinho'] ?? [];

        // 3. Se carrinho vazio, volta
        if (empty($itensCarrinho)) {
            header('Location: ' . BASE_URL . '/carrinho');
            exit;
        }

        // 4. Calcula Total
        $total = 0;
        foreach ($itensCarrinho as $item) {
            $total += ($item['preco'] * $item['qtd']);
        }

        // 5. Renderiza a View correta (sem lógica, só HTML)
        render('carrinho/finalizar', [
            'titulo' => 'Finalizar Pedido',
            'itens' => $itensCarrinho,
            'total' => $total
        ]);
    }
}
