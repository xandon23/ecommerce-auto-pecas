<?php

class Produto
{

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function listar()
    {
        $sql = "SELECT 
                    id_produto AS id,             
                    id_categoria AS categoria_id,
                    nome, 
                    descricao,
                    imagem,                       
                    preco, 
                    estoque
                FROM produtos
                ORDER BY nome";

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarPorCategoria($idCategoria)
    {
        $sql = "SELECT 
                    id_produto AS id,             
                    id_categoria AS categoria_id,
                    nome, 
                    descricao,
                    imagem,                       
                    preco, 
                    estoque
                FROM produtos 
                WHERE id_categoria = :id
                ORDER BY nome";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idCategoria);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarProdutos($termo)
    {
        $sql = "SELECT 
                    id_produto AS id,             
                    id_categoria AS categoria_id,
                    nome, 
                    descricao,
                    imagem,                       
                    preco, 
                    estoque
                FROM produtos 
                WHERE nome LIKE :termo OR descricao LIKE :termo
                ORDER BY nome";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':termo', "%{$termo}%");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getDado($id)
    {
        $sql = "SELECT 
                    id_produto AS id,             
                    id_categoria AS categoria_id,
                    nome, 
                    descricao,
                    imagem,                       
                    preco, 
                    estoque
                FROM produtos
                WHERE id_produto = :id
                LIMIT 1";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(':id', $id, PDO::PARAM_INT);
        $st->execute();
        return $st->fetch(PDO::FETCH_OBJ);
    }

    public function salvar($dados)
    {
        $id           = $dados['id']           ?? null;
        $idCategoria  = $dados['categoria_id'] ?? null;
        $nome         = $dados['nome']         ?? null;
        $descricao    = $dados['descricao']    ?? null;
        $preco        = $dados['preco']        ?? null;
        $estoque      = $dados['estoque']      ?? null;
        $imagem       = $dados['imagem']       ?? null;

        if (empty($id)) {
            $sql = "INSERT INTO produtos (id_categoria, nome, descricao, preco, estoque, imagem) 
                    VALUES (:cat, :nome, :desc, :preco, :estoque, :img)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':cat', $idCategoria);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':desc', $descricao);
            $stmt->bindValue(':preco', $preco);
            $stmt->bindValue(':estoque', $estoque);
            $stmt->bindValue(':img', $imagem);
        } else {
            if (!empty($imagem)) {
                $sql = "UPDATE produtos SET id_categoria=:cat, nome=:nome, descricao=:desc, preco=:preco, estoque=:estoque, imagem=:img WHERE id_produto=:id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':img', $imagem);
            } else {
                $sql = "UPDATE produtos SET id_categoria=:cat, nome=:nome, descricao=:desc, preco=:preco, estoque=:estoque WHERE id_produto=:id";
                $stmt = $this->pdo->prepare($sql);
            }
            $stmt->bindValue(':cat', $idCategoria);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':desc', $descricao);
            $stmt->bindValue(':preco', $preco);
            $stmt->bindValue(':estoque', $estoque);
            $stmt->bindValue(':id', $id);
        }
        return $stmt->execute();
    }

    public function excluir($id)
    {
        $sql = "DELETE FROM produtos WHERE id_produto = :id LIMIT 1";
        $consulta = $this->pdo->prepare($sql);
        $consulta->bindValue(":id", $id);
        return $consulta->execute();
    }
}
