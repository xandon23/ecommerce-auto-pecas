<?php
    class Produto {

        private $pdo;

        public function __construct($pdo)
        {
            $this->pdo = $pdo;
        }

        public function salvar($dados) {
            // insert - o id for vazio
            // update - quanto tiver imagem
            // update - quanto não existir imagem
            if(empty($dados["id"])) {
                // insert
                $sql = "insert into produto (nome, categoria_id, descricao, imagem, preco, estoque)
                values (:nome, :categoria_id, :descricao, :imagem, :preco, :estoque)";
                $consulta = $this->pdo->prepare($sql);
                $consulta->bindParam(":nome", $dados["nome"]);
                $consulta->bindParam(":categoria_id", $dados["categoria_id"]);
                $consulta->bindParam(":descricao", $dados["descricao"]);
                $consulta->bindParam(":imagem", $dados["imagem"]);
                $consulta->bindParam(":preco", $dados["preco"]);
                $consulta->bindParam(":estoque", $dados["estoque"]);
            } else if (!empty($dados["imagem"])) {
                //update com imagem
                $sql = "update produto set nome = :nome, categoria_id = :categoria_id, 
                descricao = :descricao, imagem = :imagem, preco = :preco, estoque = :estoque
                where id = :id limit 1";
                $consulta = $this->pdo->prepare($sql);
                $consulta->bindParam(":nome", $dados["nome"]);
                $consulta->bindParam(":categoria_id", $dados["categoria_id"]);
                $consulta->bindParam(":descricao", $dados["descricao"]);
                $consulta->bindParam(":imagem", $dados["imagem"]);
                $consulta->bindParam(":preco", $dados["preco"]);
                $consulta->bindParam(":estoque", $dados["estoque"]);
                $consulta->bindParam(":id", $dados["id"]);
            } else {
                //update sem imagem
                $sql = "update produto set nome = :nome, categoria_id = :categoria_id, 
                descricao = :descricao, preco = :preco, estoque = :estoque
                where id = :id limit 1";
                $consulta = $this->pdo->prepare($sql);
                $consulta->bindParam(":nome", $dados["nome"]);
                $consulta->bindParam(":categoria_id", $dados["categoria_id"]);
                $consulta->bindParam(":descricao", $dados["descricao"]);
                $consulta->bindParam(":preco", $dados["preco"]);
                $consulta->bindParam(":estoque", $dados["estoque"]);
                $consulta->bindParam(":id", $dados["id"]);
            }

            return $consulta->execute();
        }

        public function listar() {
            $sql = "select * from produto order by nome";
            $consulta = $this->pdo->prepare($sql);
            $consulta->execute();

            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }

        public function getDado($id) {
            $sql = "select * from produto where id = :id limit 1";
            $consulta = $this->pdo->prepare($sql);
            $consulta->bindParam(":id", $id);
            $consulta->execute();

            return $consulta->fetch(PDO::FETCH_OBJ);
        }

        public function getDados($id) {
            $sql = "select produto_id from item where produto_id = :id limit 1";
            $consulta = $this->pdo->prepare($sql);
            $consulta->bindParam(":id", $id);
            $consulta->execute();

            $dados = $consulta->fetch(PDO::FETCH_OBJ);
        }

        public function excluir($id) {
            
            $sql = "delete from produto where id = :id limit 1";
            $consulta = $this->pdo->prepare($sql);
            $consulta->bindParam(":id", $id);

            return $consulta->execute();
        }

    }