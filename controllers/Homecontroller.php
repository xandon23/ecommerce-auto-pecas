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

            $nomeArquivo = null;

            // 1. Prioridade: O nome salvo no banco (do upload)
            if (!empty($r->imagem)) {
                $nomeArquivo = $r->imagem;
            } 
            // 2. Fallback: O ID (para os produtos antigos de teste: 1.jpg, 2.jpg...)
            else {
                $nomeArquivo = "{$id}.jpg";
            }

            // Monta o caminho
            $imgWeb = BASE_URL . "/img/produtos/" . $nomeArquivo;
            $imgPath = BASE_PATH . "/public/img/produtos/" . $nomeArquivo;

            // 3. Se o arquivo físico não existir, usa placeholder
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
