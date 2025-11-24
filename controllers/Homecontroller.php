<?php
class HomeController {

    public function index() {

        // 1) Conexão
        $pdo = Conexao::getInstance();

        // 2) Instancia o model corretamente
        $produtoModel = new Produto($pdo);

        // 3) Busca lista de produtos no banco
        $rows = $produtoModel->listar();

        // Se der erro e voltar null, coloca array vazio
        if (!is_array($rows)) {
            $rows = [];
        }

        // 4) Monta array final para a view
        $produtos = array_map(function ($r) {

            // ID (id_produto AS id)
            $id = (int)$r->id;

            // Caminho físico do arquivo
            $imgPath = BASE_PATH . "/public/img/produtos/{$id}.jpg";

            // Caminho público (URL)
            $imgWeb  = BASE_URL . "/img/produtos/{$id}.jpg";

            // Se o arquivo não existe → usa placeholder
            if (!file_exists($imgPath)) {
                $imgWeb = BASE_URL . "/img/placeholder.jpg";
            }

            return [
                'nome'      => $r->nome,
                'preco'     => (float)$r->preco,
                'preco_old' => null,
                'img'       => $imgWeb,
                'slug'      => (string)$id,
            ];

        }, $rows);
        $produtosDestaque = $produtos;
        shuffle($produtosDestaque);
        $produtosDestaque = array_slice($produtosDestaque, 0, 10);


     render('menu/home', [
    'titulo'   => 'Home',
    'produtos' => $produtos,
    'carrossel' => $produtosDestaque
]);

    }
}
