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
        // Busca os dados do produto pelo ID
        $dadosProduto = $this->produto->getDado($id);

        // Se não achar o produto, redireciona ou mostra erro
        if (!$dadosProduto) {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }

        // Tratamento da imagem para a View de detalhes
        $imgWeb = BASE_URL . "/img/produtos/{$id}.jpg";
        $imgPath = BASE_PATH . "/public/img/produtos/{$id}.jpg";
        if (!file_exists($imgPath)) {
            $imgWeb = BASE_URL . "/img/placeholder.jpg";
        }
        // Adiciona a propriedade 'img' ao objeto para a view usar
        $dadosProduto->img = $imgWeb;

        // Renderiza a view 'views/produto/index.phtml'
        render('produto/index', [
            'titulo' => $dadosProduto->nome,
            'produto' => $dadosProduto
        ]);
    }

    public function salvar()
    {
        //print_r($_POST);
        //print_r($_FILES);
        if (!empty($_FILES["imagem"]["name"])) {
            //nome para o arquivo
            $imagem = time() . ".jpg";
            //mover o arquivo para o servidor
            if (!move_uploaded_file($_FILES["imagem"]["tmp_name"], "arquivos/{$imagem}")) {
                echo "<script>mensagem('Erro ao copiar imagem','error','')</script>";
                exit;
            }
            $_POST["imagem"] = $imagem;
        }

        // 1.600,90 -> 1600,90 -> 1600.90
        $valor = str_replace(".", "", $_POST["valor"]);
        $valor = str_replace(",", ".", $valor);

        $_POST["valor"] = $valor;

        $msg = $this->produto->salvar($_POST);

        if ($msg == 1) {
            echo "<script>mensagem('Registro salvo','ok','produto/listar')</script>";
            exit;
        } else {
            echo "<script>mensagem('Erro ao salvar', 'error','')</script>";
            exit;
        }
    }

    public function listar()
    {
        // Busca todos os produtos
        $lista = $this->produto->listar();
        
        // Processa as imagens para cada produto da lista
        foreach ($lista as $k => $item) {
            $id = $item->id; // ou $item->id_produto dependendo do seu Model
            
            $imgWeb = BASE_URL . "/img/produtos/{$id}.jpg";
            $imgPath = BASE_PATH . "/public/img/produtos/{$id}.jpg";
            
            if (!file_exists($imgPath)) {
                $imgWeb = BASE_URL . "/img/placeholder.jpg";
            }
            $lista[$k]->img = $imgWeb;
        }

        // Renderiza a view 'views/produto/listar.phtml'
        render('produto/listar', [
            'titulo' => 'Todos os Produtos',
            'produtos' => $lista
        ]);
    }

    public function excluir($id)
    {
        $dados = $this->produto->getDados($id);

        if (!empty($dados->produto_id)) {
            echo "<script>mensagem('Este produto não pode ser excluído pois tem uma venda com ele','error','')</script>";
            exit;
        }


        $msg = $this->produto->excluir($id);
        if ($msg == 1) {
            echo "<script>mensagem('Excluído com sucesso','ok','produto/listar')</script>";
        } else {
            echo "<script>mensagem('Erro ao excluir','error','')</script>";
        }
    }
}
