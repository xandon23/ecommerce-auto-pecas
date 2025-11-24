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
        $preco = $_POST["preco"];

        // Se o número tiver uma vírgula (Formato Brasileiro: 1.200,50 ou 649,90)
        if (strpos($preco, ',') !== false) {
            $preco = str_replace(".", "", $preco); // Remove o ponto de milhar
            $preco = str_replace(",", ".", $preco); // Troca a vírgula pelo ponto decimal
        }
        
        $_POST["preco"] = $preco;

        // 2. Lógica de Upload da Imagem
        // Verifica se uma imagem foi enviada
        if (!empty($_FILES['imagem']['name'])) {
            
            // Define um nome único para não substituir outras (timestamp)
            $nomeArquivo = time() . ".jpg";
            
            // Define o caminho ABSOLUTO da pasta de destino
            // BASE_PATH vem do index.php (C:/xampp/htdocs/ecommerce-auto-pecas)
            $diretorioDestino = BASE_PATH . "/public/img/produtos/";

            // Se a pasta não existir, cria-a automaticamente
            if (!is_dir($diretorioDestino)) {
                mkdir($diretorioDestino, 0777, true);
            }

            $caminhoCompleto = $diretorioDestino . $nomeArquivo;

            // Tenta mover o arquivo
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
                // Se funcionou, adiciona o nome do arquivo ao POST para salvar no banco
                $_POST['imagem'] = $nomeArquivo; // Salva no banco apenas "176395...jpg"
            } else {
                echo "<script>alert('Erro ao fazer upload da imagem.'); history.back();</script>";
                exit;
            }
        }

        // 3. Salva no Banco de Dados
        // (Chama o Model Produto)
        $msg = $this->produto->salvar($_POST);

        if ($msg) { // Se retornou true ou 1
            echo "<script>alert('Produto salvo com sucesso!'); location.href='".BASE_URL."/admin/produtos';</script>";
        } else {
            echo "<script>alert('Erro ao salvar no banco de dados.'); history.back();</script>";
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
        $dados = $this->produto->getDado($id);

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

    public function novo()
    {
        // Verifica se é admin (Segurança)
        if (empty($_SESSION['usuario']['tipo']) || $_SESSION['usuario']['tipo'] !== 'admin') {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }

        $categorias = $this->categoria->listar();
        render('admin/formulario_produto', [
            'titulo' => 'Novo Produto',
            'categorias' => $categorias,
            'produto' => null // Nulo porque é novo
        ]);
    }

    public function editar($id)
    {
        if (empty($_SESSION['usuario']['tipo']) || $_SESSION['usuario']['tipo'] !== 'admin') {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }

        $produto = $this->produto->getDado($id);
        $categorias = $this->categoria->listar();

        render('admin/formulario_produto', [
            'titulo' => 'Editar Produto',
            'categorias' => $categorias,
            'produto' => $produto
        ]);
    }
}
