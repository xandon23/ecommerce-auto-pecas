<?php
class HomeController {

    public function index() {
        
        $pdo = Conexao::getInstance();
        $produtoModel = new Produto($pdo);
        
        // Busca dados
        $rows = $produtoModel->listar();

        // Mapeia para as chaves que a view usa
        $produtos = array_map(function ($r) {
            
            // --- CORREÇÃO BLINDADA ---
            // Tenta pegar 'id'. Se não existir, tenta pegar 'id_produto'.
            // O operador '??' faz essa verificação automática.
            $id = $r->id ?? $r->id_produto ?? null;

            // Se mesmo assim for nulo, temos um problema grave, mas evitamos o erro fatal
            if (!$id) return null;
            // -------------------------
            
            // Lógica de Imagem
            $nomeArquivo = null;
            if (!empty($r->imagem)) {
                $nomeArquivo = $r->imagem;
            } else {
                $nomeArquivo = "{$id}.jpg";
            }

            $imgWeb = BASE_URL . "/img/produtos/" . $nomeArquivo;
            $imgPath = BASE_PATH . "/public/img/produtos/" . $nomeArquivo;

            if (!file_exists($imgPath)) {
                $imgWeb = BASE_URL . "/img/placeholder.jpg";
            }

            return [
                'nome'       => $r->nome,
                'preco'      => (float)$r->preco,
                'preco_old'  => null,
                'img'        => $imgWeb,
                'slug'       => (string)$id, // Usa o ID encontrado
            ];
        }, $rows);
        
        // Remove itens nulos (caso algum produto tenha vindo sem ID)
        $produtos = array_filter($produtos);

        // Lógica do Carrossel (Destaques)
        $produtosDestaque = $produtos;
        shuffle($produtosDestaque);
        $produtosDestaque = array_slice($produtosDestaque, 0, 10);

        // Busca categorias para o menu
        $categoriaModel = new Categoria($pdo);
        $listaCategorias = $categoriaModel->listar();

        render('menu/home', [
            'titulo'     => 'Home',
            'produtos'   => $produtos,
            'carrossel'  => $produtosDestaque,
            'categorias' => $listaCategorias
        ]);
    }
}
