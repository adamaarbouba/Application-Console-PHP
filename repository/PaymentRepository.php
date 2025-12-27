<?php


require_once "./config/Database.php";
require_once "BaseRepository.php";

class PaymentRepository implements BaseRepository
{

    private $conn;


    public function __construct()
    {
        $this->conn = new Database()->getConnection();
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

    public function create($payment)
    {

        $query = "insert into paiements(montant,status, commande_id) 
        values(:montant,:status,:commande_id)";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":montant" => $payment->montant,
                ":status" => $payment->status,
                ":commande_id" => $payment->commande->id,
            ]);

            (int) $id = $this->conn->lastInsertId();

            if ($id) {
                $payment->setId($id);

                if ($payment instanceof Carte) {
                    $query = "insert into cartebancaires(paiment_id, creditCardNumber) 
                      values(:paiment_id, :creditCardNumber)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([
                        ":paiment_id" => $payment->id,
                        ":creditCardNumber" => $payment->creditCardNumber
                    ]);
                }else if ($payment instanceof PayPal) {

                   $query = "insert into paypals(paiment_id, paymentEmail, paymentPassword) 
                      values(:paiment_id, :paymentEmail, :paymentPassword)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([
                        ":paiment_id" => $payment->id,
                        ":paymentEmail" => $payment->paymentEmail,
                        ":paymentPassword" => $payment->paymentPassword
                    ]);
                }else{

                    $query = "insert into virements(paiment_id, rib) 
                      values(:paiment_id, :rib)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([
                        ":paiment_id" => $payment->id,
                        ":rib" => $payment->rib
                    ]);
                }


                return $payment;
            }


            throw new EntityCreationException(" Payment creation error ", 403);
        } catch (\Throwable $th) {
            throw new EntityCreationException(" Payment creation error ".$th->getMessage(), 403);
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
