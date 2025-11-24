<?php

class Produto
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function salvar($dados) 
    {
        // 1. Ajuste dos dados
        $id           = $dados['id']           ?? null;
        $idCategoria  = $dados['categoria_id'] ?? null;
        $nome         = $dados['nome']         ?? null;
        $descricao    = $dados['descricao']    ?? null;
        $preco        = $dados['preco']        ?? null;
        $estoque      = $dados['estoque']      ?? null;
        $imagem       = $dados['imagem']       ?? null; // O nome do arquivo (ex: 1763...jpg)

        // 2. Verifica se é INSERT (Novo) ou UPDATE (Edição)
        if (empty($id)) {
            // --- INSERIR NOVO ---
            $sql = "INSERT INTO produtos (id_categoria, nome, descricao, preco, estoque, imagem) 
                    VALUES (:cat, :nome, :desc, :preco, :estoque, :img)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':cat', $idCategoria);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':desc', $descricao);
            $stmt->bindValue(':preco', $preco);
            $stmt->bindValue(':estoque', $estoque);
            $stmt->bindValue(':img', $imagem); // Aqui salvamos o nome do arquivo!

        } else {
            // --- ATUALIZAR EXISTENTE ---
            
            // Se veio uma nova imagem, atualizamos a coluna imagem
            if (!empty($imagem)) {
                $sql = "UPDATE produtos SET 
                            id_categoria = :cat, 
                            nome = :nome, 
                            descricao = :desc, 
                            preco = :preco, 
                            estoque = :estoque,
                            imagem = :img 
                        WHERE id_produto = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':img', $imagem);
            } 
            // Se NÃO veio imagem nova, NÃO tocamos na coluna imagem (mantém a antiga)
            else {
                $sql = "UPDATE produtos SET 
                            id_categoria = :cat, 
                            nome = :nome, 
                            descricao = :desc, 
                            preco = :preco, 
                            estoque = :estoque 
                        WHERE id_produto = :id";
                $stmt = $this->pdo->prepare($sql);
            }

            // Binds comuns ao UPDATE
            $stmt->bindValue(':cat', $idCategoria);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':desc', $descricao);
            $stmt->bindValue(':preco', $preco);
            $stmt->bindValue(':estoque', $estoque);
            $stmt->bindValue(':id', $id);
        }

        return $stmt->execute();
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
                    imagem,
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
