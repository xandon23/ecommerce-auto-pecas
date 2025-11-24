<?php

class Produto
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Insere ou atualiza um produto
     * $dados pode vir com:
     *  - id  OU id_produto
     *  - categoria_id OU id_categoria
     */
    public function salvar($dados)
    {
        // Normaliza nomes de campos
        $id          = $dados['id']          ?? $dados['id_produto']   ?? null;
        $idCategoria = $dados['categoria_id']?? $dados['id_categoria'] ?? null;
        $nome        = $dados['nome']        ?? null;
        $descricao   = $dados['descricao']   ?? null;
        $preco       = $dados['preco']       ?? null;
        $estoque     = $dados['estoque']     ?? null;

        if (empty($id)) {
            // INSERT em 'produtos'
            $sql = "INSERT INTO produtos 
                        (id_categoria, nome, descricao, preco, estoque)
                    VALUES 
                        (:id_categoria, :nome, :descricao, :preco, :estoque)";
            $consulta = $this->pdo->prepare($sql);
        } else {
            // UPDATE em 'produtos'
            $sql = "UPDATE produtos SET 
                        id_categoria = :id_categoria,
                        nome         = :nome,
                        descricao    = :descricao,
                        preco        = :preco,
                        estoque      = :estoque
                    WHERE id_produto = :id_produto
                    LIMIT 1";
            $consulta = $this->pdo->prepare($sql);
            $consulta->bindParam(':id_produto', $id);
        }

        $consulta->bindParam(':id_categoria', $idCategoria);
        $consulta->bindParam(':nome',         $nome);
        $consulta->bindParam(':descricao',    $descricao);
        $consulta->bindParam(':preco',        $preco);
        $consulta->bindParam(':estoque',      $estoque);

        return $consulta->execute();
    }

    /**
     * Lista todos os produtos
     */
    public function listar()
    {
        $sql = "SELECT 
                    id_produto   AS id,
                    id_categoria AS categoria_id,
                    nome,
                    descricao,
                    preco,
                    estoque
                FROM produtos
                ORDER BY nome";

        $st = $this->pdo->prepare($sql);
        $st->execute();

        return $st->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Busca um único produto pelo ID
     */
    public function getDado($id)
    {
        $sql = "SELECT 
                    id_produto   AS id,
                    id_categoria AS categoria_id,
                    nome,
                    descricao,
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

    /**
     * Exclui um produto
     */
    public function excluir($id)
    {
        $sql = "DELETE FROM produtos 
                WHERE id_produto = :id 
                LIMIT 1";

        $consulta = $this->pdo->prepare($sql);
        $consulta->bindParam(':id', $id, PDO::PARAM_INT);

        return $consulta->execute();
    }
}
