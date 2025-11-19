<?php

class Pedido {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Salva o carrinho atual como um pedido "Pendente"
    public function salvarCarrinho($idUsuario, $itens) {
        if (empty($itens)) return false;

        try {
            $this->pdo->beginTransaction();

            // 1. Verifica se já existe um pedido Pendente antigo e apaga (para substituir pelo novo)
            // Isso simplifica a lógica: o carrinho novo sempre vence.
            $sqlLimpa = "DELETE FROM pedidos WHERE id_usuario = :user AND status = 'Pendente'";
            $stmt = $this->pdo->prepare($sqlLimpa);
            $stmt->bindValue(':user', $idUsuario);
            $stmt->execute();

            // 2. Cria o novo Pedido (Cabeçalho)
            // Vamos calcular o total
            $total = 0;
            foreach($itens as $i) $total += ($i['preco'] * $i['qtd']);

            $sqlPedido = "INSERT INTO pedidos (id_usuario, data_pedido, valor_total, status, id_endereco_entrega) 
                          VALUES (:user, NOW(), :total, 'Pendente', 1)"; 
                          // Nota: coloquei id_endereco_entrega = 1 fixo por enquanto para não quebrar, 
                          // pois ainda não temos gestão de endereços.
            
            $stmt = $this->pdo->prepare($sqlPedido);
            $stmt->bindValue(':user', $idUsuario);
            $stmt->bindValue(':total', $total);
            $stmt->execute();
            
            $idPedido = $this->pdo->lastInsertId();

            // 3. Insere os Itens
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

    // Recupera o último carrinho salvo
    public function recuperarCarrinho($idUsuario) {
        // Busca pedido pendente
        $sql = "SELECT id_pedido FROM pedidos WHERE id_usuario = :user AND status = 'Pendente' ORDER BY id_pedido DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user', $idUsuario);
        $stmt->execute();
        $pedido = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$pedido) return [];

        // Busca os itens desse pedido
        $sqlItens = "SELECT ip.quantidade as qtd, p.id_produto as id, p.nome, p.preco 
                     FROM itens_pedido ip
                     JOIN produtos p ON ip.id_produto = p.id_produto
                     WHERE ip.id_pedido = :pedido";
        
        $stmtItens = $this->pdo->prepare($sqlItens);
        $stmtItens->bindValue(':pedido', $pedido->id_pedido);
        $stmtItens->execute();
        
        $itensFormatados = [];
        $resultados = $stmtItens->fetchAll(PDO::FETCH_OBJ);

        foreach($resultados as $r) {
            // Reconstrói a estrutura do carrinho (incluindo imagem)
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
}