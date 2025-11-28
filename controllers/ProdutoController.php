<?php

class ProdutoController
{

    private $produto;
    private $categoria;

    public function __construct()
    {
        $pdo = Conexao::getInstance();
        $this->produto = new Produto($pdo);
        $this->categoria = new Categoria($pdo);
    }

    public function index($id)
    {
        $dadosProduto = $this->produto->getDado($id);

        if (!$dadosProduto) {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }

        $idProd = $dadosProduto->id ?? $dadosProduto->id_produto;
        $dadosProduto->id = $idProd;

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

    public function listar()
    {
        $lista = $this->produto->listar();
        $this->processarListaParaView($lista);

        render('produto/listar', [
            'titulo' => 'Todos os Produtos',
            'produtos' => $lista
        ]);
    }

    public function categoria($id)
    {
        $lista = $this->produto->buscarPorCategoria($id);

        if (empty($lista)) {
            render('produto/listar', [
                'titulo' => 'Categoria',
                'produtos' => []
            ]);
            return;
        }

        $this->processarListaParaView($lista);

        render('produto/listar', [
            'titulo' => 'Categoria',
            'produtos' => $lista
        ]);
    }

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

    private function processarListaParaView(&$lista)
    {
        foreach ($lista as $k => $item) {

            $idProd = $item->id ?? $item->id_produto;
            $lista[$k]->id = $idProd;

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

    public function salvar()
    {
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
            echo "<script>alert('Produto salvo!'); location.href='" . BASE_URL . "/admin/produtos';</script>";
        } else {
            echo "<script>alert('Erro ao salvar'); history.back();</script>";
        }
    }

    public function excluir($id)
    {
        $this->produto->excluir($id);
        header('Location: ' . BASE_URL . '/admin/produtos');
    }

    public function novo()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['usuario']['tipo']) || $_SESSION['usuario']['tipo'] !== 'admin') {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }

        $categorias = $this->categoria->listar();

        render('admin/formulario_produto', [
            'titulo' => 'Novo Produto',
            'categorias' => $categorias,
            'produto' => null
        ]);
    }

    public function editar($id)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['usuario']['tipo']) || $_SESSION['usuario']['tipo'] !== 'admin') {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }

        $produto = $this->produto->getDado($id);

        if (!$produto) {
            echo "<script>alert('Produto não encontrado.'); location.href='" . BASE_URL . "/admin/produtos';</script>";
            exit;
        }

        $produto->preco = number_format($produto->preco, 2, ',', '.');

        $idProd = $produto->id ?? $produto->id_produto;
        $produto->id = $idProd;

        $nomeArquivo = !empty($produto->imagem) ? $produto->imagem : "{$idProd}.jpg";
        $imgPath = BASE_PATH . "/public/img/produtos/" . $nomeArquivo;

        if (file_exists($imgPath)) {
            $produto->img = BASE_URL . "/img/produtos/" . $nomeArquivo;
        } else {
            $produto->img = null;
        }

        $categorias = $this->categoria->listar();

        render('admin/formulario_produto', [
            'titulo' => 'Editar Produto',
            'categorias' => $categorias,
            'produto' => $produto
        ]);
    }
}
