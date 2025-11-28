<?php
class HomeController
{

    public function index()
    {

        $pdo = Conexao::getInstance();
        $produtoModel = new Produto($pdo);

        $rows = $produtoModel->listar();

        $produtos = array_map(function ($r) {

            $id = $r->id ?? $r->id_produto ?? null;

            if (!$id) return null;

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
                'slug'       => (string)$id,
            ];
        }, $rows);

        $produtos = array_filter($produtos);

        $produtosDestaque = $produtos;
        shuffle($produtosDestaque);
        $produtosDestaque = array_slice($produtosDestaque, 0, 10);

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
