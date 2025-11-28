<?php

class Pedido
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function salvarCarrinho($idUsuario, $itens)
    {
        if (empty($itens)) return false;

        try {
            $this->pdo->beginTransaction();

            $sqlLimpa = "DELETE FROM pedidos WHERE id_usuario = :user AND status = 'Pendente'";
            $stmt = $this->pdo->prepare($sqlLimpa);
            $stmt->bindValue(':user', $idUsuario);
            $stmt->execute();

            $total = 0;
            foreach ($itens as $i) $total += ($i['preco'] * $i['qtd']);

            $sqlPedido = "INSERT INTO pedidos (id_usuario, data_pedido, valor_total, status, id_endereco_entrega) 
                          VALUES (:user, NOW(), :total, 'Pendente', 1)";

            $stmt = $this->pdo->prepare($sqlPedido);
            $stmt->bindValue(':user', $idUsuario);
            $stmt->bindValue(':total', $total);
            $stmt->execute();

            $idPedido = $this->pdo->lastInsertId();

            $sqlItem = "INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario_na_compra) 
                        VALUES (:pedido, :produto, :qtd, :preco)";
            $stmtItem = $this->pdo->prepare($sqlItem);

            foreach ($itens as $item) {
                $stmtItem->bindValue(':pedido', $idPedido);
                $stmtItem->bindValue(':produto', $item['id']);
                $stmtItem->bindValue(':qtd', $item['qtd']);
                $stmtItem->bindValue(':preco', $item['preco']);
                $stmtItem->execute();
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function recuperarCarrinho($idUsuario)
    {
        $sql = "SELECT id_pedido FROM pedidos WHERE id_usuario = :user AND status = 'Pendente' ORDER BY id_pedido DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user', $idUsuario);
        $stmt->execute();
        $pedido = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$pedido) return [];

        $sqlItens = "SELECT ip.quantidade as qtd, p.id_produto as id, p.nome, p.preco 
                     FROM itens_pedido ip
                     JOIN produtos p ON ip.id_produto = p.id_produto
                     WHERE ip.id_pedido = :pedido";

        $stmtItens = $this->pdo->prepare($sqlItens);
        $stmtItens->bindValue(':pedido', $pedido->id_pedido);
        $stmtItens->execute();

        $itensFormatados = [];
        $resultados = $stmtItens->fetchAll(PDO::FETCH_OBJ);

        foreach ($resultados as $r) {
            $imgWeb = BASE_URL . "/img/produtos/{$r->id}.jpg";
            $imgPath = BASE_PATH . "/public/img/produtos/{$r->id}.jpg";
            if (!file_exists($imgPath)) {
                $imgWeb = BASE_URL . "/img/placeholder.jpg";
            }

            $itensFormatados[$r->id] = [
                'id'    => $r->id,
                'nome'  => $r->nome,
                'preco' => $r->preco,
                'img'   => $imgWeb,
                'qtd'   => $r->qtd
            ];
        }

        return $itensFormatados;
    }

    public function finalizarPedido($idUsuario, $itens, $idEndereco)
    {
        try {
            $this->pdo->beginTransaction();

            $sqlDel = "DELETE FROM pedidos WHERE id_usuario = :user AND status = 'Pendente'";
            $stDel = $this->pdo->prepare($sqlDel);
            $stDel->bindValue(':user', $idUsuario);
            $stDel->execute();

            $total = 0;
            foreach ($itens as $i) $total += ($i['preco'] * $i['qtd']);

            $sql = "INSERT INTO pedidos (id_usuario, data_pedido, valor_total, status, id_endereco_entrega) 
                    VALUES (:user, NOW(), :total, 'Confirmado', :endereco)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':user', $idUsuario);
            $stmt->bindValue(':total', $total);
            $stmt->bindValue(':endereco', $idEndereco);
            $stmt->execute();

            $idPedido = $this->pdo->lastInsertId();

            $sqlItem = "INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario_na_compra) 
                        VALUES (:pedido, :prod, :qtd, :preco)";

            $sqlStock = "UPDATE produtos SET estoque = estoque - :qtd WHERE id_produto = :prod";

            $stmtItem = $this->pdo->prepare($sqlItem);
            $stmtStock = $this->pdo->prepare($sqlStock);

            foreach ($itens as $item) {
                $stmtItem->bindValue(':pedido', $idPedido);
                $stmtItem->bindValue(':prod', $item['id']);
                $stmtItem->bindValue(':qtd', $item['qtd']);
                $stmtItem->bindValue(':preco', $item['preco']);
                $stmtItem->execute();

                $stmtStock->bindValue(':qtd', $item['qtd']);
                $stmtStock->bindValue(':prod', $item['id']);
                $stmtStock->execute();
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function listarPorUsuario($idUsuario)
    {
        $sql = "SELECT * FROM pedidos 
                WHERE id_usuario = :id 
                ORDER BY data_pedido DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idUsuario);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getItensDoPedido($idPedido)
    {
        $sql = "SELECT ip.*, p.nome, p.imagem 
                FROM itens_pedido ip
                JOIN produtos p ON ip.id_produto = p.id_produto
                WHERE ip.id_pedido = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idPedido);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
