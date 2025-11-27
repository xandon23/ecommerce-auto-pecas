<?php
class CarrinhoController
{

    public function adicionar($id) {
        $pdo = Conexao::getInstance();
        $produtoModel = new Produto($pdo);
        
        // Busca o produto para ver o estoque atual
        $produto = $produtoModel->getDado($id);

        if ($produto) {
            // 1. VERIFICAÇÃO DE ESTOQUE INICIAL
            if ($produto->estoque <= 0) {
                echo "<script>alert('Produto esgotado!'); location.href='".BASE_URL."/home';</script>";
                exit;
            }

            if (!isset($_SESSION['carrinho'])) {
                $_SESSION['carrinho'] = [];
            }

            // Calcula quanto o cliente quer comprar (o que já tem + 1)
            $qtdAtualNoCarrinho = isset($_SESSION['carrinho'][$id]) ? $_SESSION['carrinho'][$id]['qtd'] : 0;
            $qtdDesejada = $qtdAtualNoCarrinho + 1;

            // 2. VERIFICAÇÃO DE LIMITE
            if ($qtdDesejada > $produto->estoque) {
                echo "<script>alert('Estoque insuficiente! Temos apenas {$produto->estoque} unidades.'); location.href='".BASE_URL."/carrinho';</script>";
                exit;
            }

            // Se passou nos testes, adiciona
            if (isset($_SESSION['carrinho'][$id])) {
                $_SESSION['carrinho'][$id]['qtd']++;
            } else {
                // Tratamento da imagem
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

    /**
     * Aumenta a quantidade (+1)
     */
    public function aumentar($id) {
        // 1. Verifica se o item existe no carrinho
        if (!isset($_SESSION['carrinho'][$id])) {
            header('Location: ' . BASE_URL . '/carrinho');
            exit;
        }

        // 2. Busca o produto no banco para conferir o ESTOQUE ATUAL
        $pdo = Conexao::getInstance();
        $produtoModel = new Produto($pdo);
        $produtoBanco = $produtoModel->getDado($id);

        // 3. Quantidade atual no carrinho
        $qtdAtual = $_SESSION['carrinho'][$id]['qtd'];

        // 4. Verifica se tem estoque para +1
        if ($produtoBanco && $produtoBanco->estoque > $qtdAtual) {
            $_SESSION['carrinho'][$id]['qtd']++;
        } else {
            echo "<script>alert('Desculpe, estoque máximo atingido para este produto.'); location.href='".BASE_URL."/carrinho';</script>";
            exit;
        }

        header('Location: ' . BASE_URL . '/carrinho');
        exit;
    }

    /**
     * Diminui a quantidade (-1)
     */
    public function diminuir($id) {
        if (isset($_SESSION['carrinho'][$id])) {
            $qtdAtual = $_SESSION['carrinho'][$id]['qtd'];

            if ($qtdAtual > 1) {
                // Se tiver mais de 1, apenas diminui
                $_SESSION['carrinho'][$id]['qtd']--;
            } else {
                // Se tiver 1 e clicar em menos, você quer remover?
                // Opção A: Não faz nada (fica em 1) - Vamos usar esta.
                // Opção B: Remove o item.
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

        $pdo = Conexao::getInstance();
        $enderecoModel = new Endereco($pdo);
        $meusEnderecos = $enderecoModel->listarPorUsuario($_SESSION['usuario']['id']);

        // 5. Renderiza a View correta (sem lógica, só HTML)
        render('carrinho/finalizar', [
            'titulo' => 'Finalizar Pedido',
            'itens' => $itensCarrinho,
            'total' => $total,
            'enderecos' => $meusEnderecos
        ]);
    }
    
    public function confirmar() {
        // 1. Segurança
        if (!isset($_SESSION['usuario']) || empty($_SESSION['carrinho'])) {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }

        $pdo = Conexao::getInstance();
        $pedidoModel = new Pedido($pdo);
        $produtoModel = new Produto($pdo); // Necessário para verificar estoque

        // 2. VERIFICAÇÃO FINAL DE ESTOQUE (O "Pente Fino")
        foreach ($_SESSION['carrinho'] as $id => $item) {
            $produtoReal = $produtoModel->getDado($id);
            
            // Se produto sumiu ou estoque acabou
            if (!$produtoReal || $produtoReal->estoque < $item['qtd']) {
                $estoqueAtual = $produtoReal ? $produtoReal->estoque : 0;
                echo "<script>
                    alert('Opa! O produto \"{$item['nome']}\" acabou de esgotar ou não tem quantidade suficiente. Disponível: {$estoqueAtual}.'); 
                    location.href='".BASE_URL."/carrinho';
                </script>";
                exit;
            }
        }

        // Verifica endereço
        $idEndereco = $_POST['id_endereco'] ?? null;
        if (empty($idEndereco)) {
            echo "<script>alert('Selecione um endereço de entrega.'); history.back();</script>";
            exit;
        }

        // 3. Se passou em tudo, finaliza
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
