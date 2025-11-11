<?php
class HomeController {
    public function index() {
        // 1) Pega o PDO da sua Conexao
        $pdo = Conexao::getInstance(); // ou Conexao::conn()

        // 2) Instancia o Model
        $produtoModel = new Produto($pdo);

        // 3) Busca no banco
        $rows = $produtoModel->listar(); // vem como objetos (PDO::FETCH_OBJ)

        // 4) Mapeia para as chaves que a view já usa: nome, preco, preco_old, img, slug
        $produtos = array_map(function ($r) {
            $id = (int)$r->id; // alias de id_produto (veja nota abaixo)

            // imagem por convenção; se não existir, usa placeholder
            $imgWeb  = "/img/produtos/{$id}.jpg";
            $imgPath = BASE_PATH . "/public{$imgWeb}";
            if (!file_exists($imgPath)) {
                $imgWeb = "/img/placeholder.jpg";
            }

            return [
                'nome'      => $r->nome,
                'preco'     => (float)$r->preco,
                'preco_old' => null,        // se não tiver promo no BD
                'img'       => $imgWeb,
                'slug'      => (string)$id, // a view espera "slug"; usamos o id
            ];
        }, $rows);

        // 5) Renderiza passando também os produtos (mantendo seu título)
        render('home', [
            'titulo'   => 'Home',
            'produtos' => $produtos
        ]);
    }
}
