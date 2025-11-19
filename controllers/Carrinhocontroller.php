<?php

class CarrinhoController {

    public function __construct() {
        
    }

    /**
     * Mostra a página do carrinho
     */
    public function index() {
        // Pega os produtos da sessão (ou array vazio se não tiver nada)
        $itensCarrinho = $_SESSION['carrinho'] ?? [];

        if (!empty($itensCarrinho)) {
            $pdo = Conexao::getInstance();
            $produtoModel = new Produto($pdo);
            
            $carrinhoAlterado = false;

            foreach ($itensCarrinho as $id => $item) {
                // Busca o estoque ATUAL no banco de dados
                $produtoNoBanco = $produtoModel->getDado($id);

                // Se o produto não existir mais OU o estoque for 0
                if (!$produtoNoBanco || $produtoNoBanco->estoque <= 0) {
                    // Remove do carrinho
                    unset($_SESSION['carrinho'][$id]);
                    $carrinhoAlterado = true;
                } 
                // Se a quantidade no carrinho for maior que o estoque disponível
                else if ($item['qtd'] > $produtoNoBanco->estoque) {
                    // Ajusta a quantidade para o máximo disponível
                    $_SESSION['carrinho'][$id]['qtd'] = $produtoNoBanco->estoque;
                    $carrinhoAlterado = true;
                }
            }
            
            // Se houve alterações, atualiza a variável local e avisa (opcional)
            if ($carrinhoAlterado) {
                $itensCarrinho = $_SESSION['carrinho'];
                // Aqui você poderia adicionar uma mensagem de aviso, se quisesse
                echo "<script>alert('Alguns itens foram removidos ou ajustados por falta de estoque.');</script>";
            }
        }
        
        render('carrinho', [
            'titulo' => 'Meu Carrinho',
            'itens' => $itensCarrinho
        ]);
    }

    /**
     * Adiciona um produto ao carrinho
     * URL: /carrinho/adicionar/1
     */
    public function adicionar($id) {
        // Busca o produto no banco para ter certeza que existe e pegar preço/nome
        $pdo = Conexao::getInstance();
        $produtoModel = new Produto($pdo);
        $produto = $produtoModel->getDado($id);

        if ($produto) {
            // Se o carrinho não existir, cria
            if (!isset($_SESSION['carrinho'])) {
                $_SESSION['carrinho'] = [];
            }

            // Se o produto já estiver no carrinho, aumenta a quantidade
            if (isset($_SESSION['carrinho'][$id])) {
                $_SESSION['carrinho'][$id]['qtd']++;
            } else {
                // Se não, adiciona o produto novo
                // Tratamento da imagem igual ao HomeController
                $imgWeb = BASE_URL . "/img/produtos/{$id}.jpg";
                $imgPath = BASE_PATH . "/public/img/produtos/{$id}.jpg";
                if (!file_exists($imgPath)) {
                    $imgWeb = BASE_URL . "/img/placeholder.jpg";
                }

                $_SESSION['carrinho'][$id] = [
                    'id'    => $produto->id,
                    'nome'  => $produto->nome,
                    'preco' => $produto->preco,
                    'img'   => $imgWeb,
                    'qtd'   => 1
                ];
            }
        }

        // Redireciona para a página do carrinho
        header('Location: ' . BASE_URL . '/carrinho');
        exit;
    }

    /**
     * Remove item ou limpa
     */
    public function remover($id) {

        if ($id === null || $id === '') {
            header('Location: ' . BASE_URL . '/carrinho');
            exit;
        }
        
        if (isset($_SESSION['carrinho'][$id])) {
            unset($_SESSION['carrinho'][$id]);
        }
        header('Location: ' . BASE_URL . '/carrinho');
        exit;
    }
}