<?php

class Endereco
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarPorUsuario($idUsuario)
    {
        $sql = "SELECT * FROM enderecos WHERE id_usuario = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idUsuario);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getDado($id)
    {
        $sql = "SELECT * FROM enderecos WHERE id_endereco = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function salvar($dados)
    {
        if (empty($dados['id'])) {
            $sql = "INSERT INTO enderecos (id_usuario, rua, numero, bairro, cidade, estado, cep) 
                    VALUES (:uid, :rua, :num, :bairro, :cid, :est, :cep)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':uid', $dados['id_usuario']);
        } else {
            $sql = "UPDATE enderecos SET rua=:rua, numero=:num, bairro=:bairro, cidade=:cid, estado=:est, cep=:cep 
                    WHERE id_endereco=:id AND id_usuario=:uid";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $dados['id']);
            $stmt->bindValue(':uid', $dados['id_usuario']);
        }

        $stmt->bindValue(':rua', $dados['rua']);
        $stmt->bindValue(':num', $dados['numero']);
        $stmt->bindValue(':bairro', $dados['bairro']);
        $stmt->bindValue(':cid', $dados['cidade']);
        $stmt->bindValue(':est', $dados['estado']);
        $stmt->bindValue(':cep', $dados['cep']);

        return $stmt->execute();
    }

    public function excluir($idEndereco, $idUsuario)
    {
        $sql = "DELETE FROM enderecos WHERE id_endereco = :id AND id_usuario = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idEndereco);
        $stmt->bindValue(':uid', $idUsuario);
        return $stmt->execute();
    }
}
