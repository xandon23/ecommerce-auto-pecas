<?php

class ProdutoController {
    
    private $produto;
    private $categoria;

    public function __construct()
    {
        $pdo = Conexao::getInstance();
        $this->produto = new Produto($pdo);
        $this->categoria = new Categoria($pdo);
    }

    /**
     * Página de Detalhes do Produto
     */
    public function index($id) 
    {
        $dadosProduto = $this->produto->getDado($id);

        if (!$dadosProduto) {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }

        // Tratamento de imagem e ID
        $idProd = $dadosProduto->id ?? $dadosProduto->id_produto;
        $dadosProduto->id = $idProd;

        // Lógica de Imagem
        $nomeArquivo = !empty($dadosProduto->imagem) ? $dadosProduto->imagem : "{$idProd}.jpg";
        $imgWeb = BASE_URL . "/img/produtos/" . $nomeArquivo;
        $imgPath = BASE_PATH . "/public/img/produtos/" . $nomeArquivo;

        if (!file_exists($imgPath)) {
            $imgWeb = BASE_URL . "/img/placeholder.jpg";
        }
        $dadosProduto->img = $imgWeb;

        render('produto/index', [
            'titulo' => $dadosProduto->nome,
            'produto' => $dadosProduto
        ]);
    }

    /**
     * Listagem Geral (Ver Todos)
     */
    public function listar() 
    {
        $lista = $this->produto->listar();
        $this->processarListaParaView($lista); // Usa função auxiliar abaixo

        render('produto/listar', [
            'titulo' => 'Todos os Produtos',
            'produtos' => $lista
        ]);
    }

    /**
     * Filtro por Categoria (O QUE ESTAVA A DAR ERRO)
     */
    public function categoria($id) 
    {
        // 1. Busca do banco
        $lista = $this->produto->buscarPorCategoria($id);

        // 2. Se a lista vier vazia, renderiza logo para evitar erros de loop
        if (empty($lista)) {
            render('produto/listar', [
                'titulo' => 'Categoria',
                'produtos' => []
            ]);
            return;
        }

        // 3. Processa imagens e IDs (Igual à Home)
        $this->processarListaParaView($lista);

        // 4. Renderiza
        render('produto/listar', [
            'titulo' => 'Categoria',
            'produtos' => $lista
        ]);
    }

    /**
     * Busca (Barra de Pesquisa)
     */
    public function buscar() 
    {
        $termo = $_GET['q'] ?? '';
        if (empty($termo)) {
            header('Location: ' . BASE_URL . '/produto/listar');
            exit;
        }

        $lista = $this->produto->buscarProdutos($termo);
        $this->processarListaParaView($lista);

        render('produto/listar', [
            'titulo' => 'Resultados para: ' . htmlspecialchars($termo),
            'produtos' => $lista
        ]);
    }

    // --- FUNÇÃO AUXILIAR PARA NÃO REPETIR CÓDIGO ---
    // (Aplica a mesma lógica da Home em todas as listas)
    private function processarListaParaView(&$lista) {
        foreach ($lista as $k => $item) {
            
            // 1. Garante o ID
            $idProd = $item->id ?? $item->id_produto;
            $lista[$k]->id = $idProd;

            // 2. Garante a Imagem
            $nomeArquivo = !empty($item->imagem) ? $item->imagem : "{$idProd}.jpg";
            
            $imgWeb = BASE_URL . "/img/produtos/" . $nomeArquivo;
            $imgPath = BASE_PATH . "/public/img/produtos/" . $nomeArquivo;
            
            if (file_exists($imgPath)) {
                $lista[$k]->img = $imgWeb;
            } else {
                $lista[$k]->img = BASE_URL . "/img/placeholder.jpg";
            }
        }
    }

    // --- MANTENHA AS SUAS FUNÇÕES DE SALVAR/EXCLUIR ABAIXO ---
    public function salvar() {
        // ... (seu código de salvar que já funciona) ...
        // Copie do seu arquivo atual
        $valor = str_replace(".", "", $_POST["preco"]);
        $valor = str_replace(",", ".", $valor);
        $_POST["preco"] = $valor;

        if (!empty($_FILES['imagem']['name'])) {
            $nomeArquivo = time() . ".jpg";
            $diretorioDestino = BASE_PATH . "/public/img/produtos/";
            if (!is_dir($diretorioDestino)) mkdir($diretorioDestino, 0777, true);
            
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $diretorioDestino . $nomeArquivo)) {
                $_POST['imagem'] = $nomeArquivo;
            }
        }

        $msg = $this->produto->salvar($_POST);
        
        if ($msg) {
            echo "<script>alert('Produto salvo!'); location.href='".BASE_URL."/admin/produtos';</script>";
        } else {
            echo "<script>alert('Erro ao salvar'); history.back();</script>";
        }
    }

    public function excluir($id) {
        $this->produto->excluir($id);
        header('Location: ' . BASE_URL . '/admin/produtos');
    }
}