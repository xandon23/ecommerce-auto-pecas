<?php
    require "../config/Conexao.php";
    require "../models/Produto.php";
    require "../models/Categoria.php";

    class ProdutoController {

        private $produto;
        private $categoria;

        public function __construct()
        {
            $pdo = Conexao::conectar();

            $this->produto = new Produto($pdo);
            $this->categoria = new Categoria($pdo);
        }

        public function index($id) {
            require "../view/produto/index.php";
        }

        public function salvar() {
            //print_r($_POST);
            //print_r($_FILES);
            if (!empty($_FILES["imagem"]["name"])) {
                //nome para o arquivo
                $imagem = time() . ".jpg";
                //mover o arquivo para o servidor
                if(!move_uploaded_file($_FILES["imagem"]["tmp_name"], "arquivos/{$imagem}")) {
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

        public function listar() {
            require "../view/produto/listar.php";
        }

        public function excluir($id) {
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