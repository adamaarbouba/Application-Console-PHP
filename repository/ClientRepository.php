<?php



require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/BaseRepository.php";
// require_once "../config/Database.php";

// require_once "./BaseRepository.php";

class ClientRepository implements BaseRepository
{

    public $conn;
    public $db;


    public function __construct()
    {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }


    public function findAll()
    {
        $query = "select * from clients where 1=1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);

        $clients = [];
        foreach ($result as $obj) {

            $cl = new Client($obj->name, $obj->email);
            $cl->setId($obj->id);
            array_push($clients, $cl);
        }

        return $clients;
    }

    public function findById($id)
    {

        $query = "select * from clients where id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $id
            ]);

            $row = $stmt->fetch(PDO::FETCH_OBJ);

            if (empty($row)) {
                throw new EntitySearchException(" Client search with id: " . $id . " error ", 403);
            }

            $client = new Client($row->name, $row->email);
            $client->setId($id);

            return $client;
        } catch (\Throwable $th) {
            throw new EntitySearchException(" Client search with id: " . $id . " error ", 403);
        }
    }

    public function create($client)
    {

        $query = "insert into clients(name,email) values(:name, :email)";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":name" => $client->name,
                ":email" => $client->email
            ]);

            (int) $id = $this->conn->lastInsertId();

            if ($id) {
                $client->setId($id);
                return $client;
            }

            throw new EntityCreationException(" Client creation error ", 403);
        } catch (\Throwable $th) {
            throw new EntityCreationException(" Client creation error ", 403);
        }
    }


    public function update($id)
    {


        $client = $this->findById($id);

        $query = "update clients set name =:name, email =:email where id=:id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $client->id,
                ":name" => $client->name,
                ":email" => $client->email
            ]);

            throw new EntityCreationException(" Client with id: " . $client->id . "update error", 403);
        } catch (\Throwable $th) {
            throw new EntityCreationException(" Client with id: " . $client->id . "update error", 403);
        }
    }


    public function delete($id)
    {
        $client = $this->findById($id);
    }
}
