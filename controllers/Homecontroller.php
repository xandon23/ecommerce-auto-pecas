<?php
class HomeController {
    
    public function index() {
        
        // 1) Pega o PDO da sua Conexao
        $pdo = Conexao::getInstance();
        
        // 2) Instancia o Model (CORRIGIDO)
        $produtoModel = new Produto($pdo);
        
        // 3) Busca no banco
        $rows = $produtoModel->listar(); // vem como objetos (PDO::FETCH_OBJ)

        // 4) Mapeia para as chaves que a view já usa: (CORRIGIDO)
        $produtos = array_map(function ($r) {
            
            $id = (int)$r->id; // Alias de id_produto
            
            // --- INÍCIO DA CORREÇÃO ---
            
            // 4.1. Define o caminho do ficheiro (para ver se existe)
            // BASE_PATH é 'C:\xampp\htdocs\ecommerce-auto-pecas'
            $imgPath = BASE_PATH . "/public/img/produtos/{$id}.jpg";

            // 4.2. Define o caminho do URL (o que vai para o HTML)
            // BASE_URL é '/ecommerce-auto-pecas/public'
            $imgWeb = BASE_URL . "/img/produtos/{$id}.jpg";

            // 4.3. Se o ficheiro NÃO existir no disco, usa o placeholder
            if (!file_exists($imgPath)) {
                $imgWeb = BASE_URL . "/img/placeholder.jpg"; // (Assumindo que tem um placeholder.jpg)
            }
            
            // --- FIM DA CORREÇÃO ---

            return [
                'nome'       => $r->nome,
                'preco'      => (float)$r->preco,
                'preco_old'  => null, // se não tiver promo no BD
                'img'        => $imgWeb, // AGORA ENVIA O URL COMPLETO
                'slug'       => (string)$id, // a view espera "slug", usamos o id
            ];
        }, $rows);
        
        // 5) Renderiza passando também os produtos
        render('home', [
            'titulo'   => 'Home',
            'produtos' => $produtos
        ]);
    }
}