<?php
class CarrinhoController
{

    public function adicionar($id)
    {
        $pdo = Conexao::getInstance();
        $produtoModel = new Produto($pdo);

        $produto = $produtoModel->getDado($id);

        if ($produto) {
            if ($produto->estoque <= 0) {
                echo "<script>alert('Produto esgotado!'); location.href='" . BASE_URL . "/home';</script>";
                exit;
            }

            if (!isset($_SESSION['carrinho'])) {
                $_SESSION['carrinho'] = [];
            }

            $qtdAtualNoCarrinho = isset($_SESSION['carrinho'][$id]) ? $_SESSION['carrinho'][$id]['qtd'] : 0;
            $qtdDesejada = $qtdAtualNoCarrinho + 1;

            if ($qtdDesejada > $produto->estoque) {
                echo "<script>alert('Estoque insuficiente! Temos apenas {$produto->estoque} unidades.'); location.href='" . BASE_URL . "/carrinho';</script>";
                exit;
            }

            if (isset($_SESSION['carrinho'][$id])) {
                $_SESSION['carrinho'][$id]['qtd']++;
            } else {
                $idProd = $produto->id ?? $produto->id_produto;
                $nomeArquivo = !empty($produto->imagem) ? $produto->imagem : "{$idProd}.jpg";

                $imgWeb = BASE_URL . "/img/produtos/" . $nomeArquivo;
                $imgPath = BASE_PATH . "/public/img/produtos/" . $nomeArquivo;
                if (!file_exists($imgPath)) {
                    $imgWeb = BASE_URL . "/img/placeholder.jpg";
                }

                $_SESSION['carrinho'][$id] = [
                    'id'    => $idProd,
                    'nome'  => $produto->nome,
                    'preco' => $produto->preco,
                    'img'   => $imgWeb,
                    'qtd'   => 1
                ];
            }
        }

        header('Location: ' . BASE_URL . '/carrinho');
        exit;
    }

    public function index()
    {
        $itens = $_SESSION['carrinho'] ?? [];
        render('carrinho/index', compact('itens'));
    }

    public function aumentar($id)
    {
        if (!isset($_SESSION['carrinho'][$id])) {
            header('Location: ' . BASE_URL . '/carrinho');
            exit;
        }

        $pdo = Conexao::getInstance();
        $produtoModel = new Produto($pdo);
        $produtoBanco = $produtoModel->getDado($id);

        $qtdAtual = $_SESSION['carrinho'][$id]['qtd'];

        if ($produtoBanco && $produtoBanco->estoque > $qtdAtual) {
            $_SESSION['carrinho'][$id]['qtd']++;
        } else {
            echo "<script>alert('Desculpe, estoque máximo atingido para este produto.'); location.href='" . BASE_URL . "/carrinho';</script>";
            exit;
        }

        header('Location: ' . BASE_URL . '/carrinho');
        exit;
    }

    public function diminuir($id)
    {
        if (isset($_SESSION['carrinho'][$id])) {
            $qtdAtual = $_SESSION['carrinho'][$id]['qtd'];

            if ($qtdAtual > 1) {
                $_SESSION['carrinho'][$id]['qtd']--;
            } else {
            }
        }
        header('Location: ' . BASE_URL . '/carrinho');
        exit;
    }

    public function remover($id)
    {
        if (isset($_SESSION['carrinho'][$id])) {
            unset($_SESSION['carrinho'][$id]);
        }
        header("Location: " . BASE_URL . "/carrinho");
    }

    public function finalizar()
    {
        if (!isset($_SESSION['usuario'])) {
            echo "<script>alert('Faça login para finalizar a compra.'); location.href='" . BASE_URL . "/index';</script>";
            exit;
        }

        $itensCarrinho = $_SESSION['carrinho'] ?? [];

        if (empty($itensCarrinho)) {
            header('Location: ' . BASE_URL . '/carrinho');
            exit;
        }

        $total = 0;
        foreach ($itensCarrinho as $item) {
            $total += ($item['preco'] * $item['qtd']);
        }

        $pdo = Conexao::getInstance();
        $enderecoModel = new Endereco($pdo);
        $meusEnderecos = $enderecoModel->listarPorUsuario($_SESSION['usuario']['id']);

        render('carrinho/finalizar', [
            'titulo' => 'Finalizar Pedido',
            'itens' => $itensCarrinho,
            'total' => $total,
            'enderecos' => $meusEnderecos
        ]);
    }

    public function confirmar()
    {
        if (!isset($_SESSION['usuario']) || empty($_SESSION['carrinho'])) {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }

        $pdo = Conexao::getInstance();
        $pedidoModel = new Pedido($pdo);
        $produtoModel = new Produto($pdo);
        foreach ($_SESSION['carrinho'] as $id => $item) {
            $produtoReal = $produtoModel->getDado($id);

            if (!$produtoReal || $produtoReal->estoque < $item['qtd']) {
                $estoqueAtual = $produtoReal ? $produtoReal->estoque : 0;
                echo "<script>
                    alert('Opa! O produto \"{$item['nome']}\" acabou de esgotar ou não tem quantidade suficiente. Disponível: {$estoqueAtual}.'); 
                    location.href='" . BASE_URL . "/carrinho';
                </script>";
                exit;
            }
        }

        $idEndereco = $_POST['id_endereco'] ?? null;
        if (empty($idEndereco)) {
            echo "<script>alert('Selecione um endereço de entrega.'); history.back();</script>";
            exit;
        }

        $sucesso = $pedidoModel->finalizarPedido(
            $_SESSION['usuario']['id'],
            $_SESSION['carrinho'],
            $idEndereco
        );

        if ($sucesso) {
            unset($_SESSION['carrinho']);
            render('carrinho/sucesso', ['titulo' => 'Pedido Confirmado']);
        } else {
            echo "<script>alert('Erro ao finalizar pedido.'); history.back();</script>";
        }
    }
}
