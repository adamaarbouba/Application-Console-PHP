<?php


require_once "./config/Database.php";
require_once "BaseRepository.php";

class CommandeRepository implements BaseRepository
{

    private $conn;

    // private $clientRepository; 

    public function __construct()
    {
        $this->conn = new Database()->getConnection();
        // $this->clientRepository = new ClientRepository();
    }


    public function findAll()
    {
        // $query = "select * from commandes where 1=1";
        $query = "select cm.id as cmd_id, cm.montantTotal,cm.status, cm.client_id, cl.name, cl.email from commandes cm inner join clients cl on cl.id = cm.client_id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);

        $commandes = [];
        foreach ($result as $obj) {


            // $client = $this->clientRepository->findById($obj->id);
            $client = new Client($obj->name, $obj->email);
            $client->setId($obj->client_id);

            $cmd = new Commande($obj->montantTotal, $obj->status);
            $cmd->setId($obj->cmd_id);
            $cmd->setClient($client);
            array_push($commandes, $cmd);
        }

        return $commandes;
    }

    public function findById($id)
    {

        $query = "select cm.id as cmd_id, cm.montantTotal,cm.status, cm.client_id, cl.name, cl.email 
                    from commandes cm 
                    inner join clients cl on cl.id = cm.client_id
                    where cm.id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $id
            ]);

            $row = $stmt->fetch(PDO::FETCH_OBJ);

            if (empty($row)) {
                throw new EntitySearchException(" Commande search with id: " . $id . " error ", 403);
            }

            $client = new Client($row->name, $row->email);
            $client->setId($row->client_id);

            $cmd = new Commande($row->montantTotal, $row->status);
            $cmd->setId($row->cmd_id);
            $cmd->setClient($client);

            return $cmd;
        } catch (\Throwable $th) {
            throw new EntitySearchException("Commande search with id: " . $id . " error ", 403);
        }
    }

    public function create($commande)
    {

    
        $query = "insert into Commandes(montantTotal,status, client_id) 
        values(:montantTotal, :status, :client_id)";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":client_id" => $commande->client->id,
                ":montantTotal" => $commande->montantTotal,
                ":status" => $commande->status,
            ]);

            (int) $id = $this->conn->lastInsertId();

            if ($id) {
                $commande->setId($id);
                return $commande;
            }

            throw new EntityCreationException(" Commande creation error ", 403);
        } catch (\Throwable $th) {
            throw new EntityCreationException(" Commande creation error ", 403);
        }
    }


    public function update($commande)
    {

        $query = "update commandes set status=:status where id=:id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $commande->id,
                ":status" => $commande->status,
            ]);

            return $commande;

        } catch (\Throwable $th) {
            throw new EntityCreationException(" commande with id: " . $commande->id . "update error: ".$th->getMessage(), 403);
        }
    }


    public function delete($id)
    {
        $client = $this->findById($id);
    }
}
