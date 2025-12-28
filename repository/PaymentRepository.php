<?php

require_once "./config/Database.php";
require_once "BaseRepository.php";
require_once "./entity/Virement.php";
require_once "./entity/Carte.php";
require_once "./entity/PayPal.php";
require_once "./exception/EntitySearchException.php";
require_once "./exception/EntityCreationException.php";

class PaymentRepository implements BaseRepository
{
    private $conn;
    private $db;

    public function __construct()
    {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function findAll()
    {
        return [];
    }

    public function findById($id)
    {
        $query = "SELECT p.*, 
                         v.rib, 
                         cb.creditCardNumber, 
                         py.paymentEmail, py.paymentPassword
                  FROM paiements p
                  LEFT JOIN virements v ON p.id = v.paiment_id
                  LEFT JOIN cartebancaires cb ON p.id = cb.paiment_id
                  LEFT JOIN paypals py ON p.id = py.paiment_id
                  WHERE p.id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([":id" => $id]);
            $row = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$row) {
                throw new EntitySearchException("Payment with ID $id not found.", 404);
            }

            if ($row->rib) {
                $payment = new Virement($row->montant, $row->rib);
            } elseif ($row->creditCardNumber) {
                $payment = new Carte($row->montant, $row->creditCardNumber);
            } elseif ($row->paymentEmail) {
                $payment = new PayPal($row->montant, $row->paymentEmail, $row->paymentPassword);
            } else {
                $payment = new Virement($row->montant, "N/A");
            }

            $payment->setId($row->id);
            $payment->setStatus($row->status);

            return $payment;
        } catch (\Throwable $th) {
            throw new EntitySearchException("Error searching for payment: " . $th->getMessage(), 403);
        }
    }

    public function create($payment)
    {
        $query = "INSERT INTO paiements(montant, status, commande_id) 
                  VALUES(:montant, :status, :commande_id)";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":montant" => $payment->getMontant(),
                ":status" => $payment->getStatus(),
                ":commande_id" => $payment->getCommande()->id,
            ]);

            (int) $id = $this->conn->lastInsertId();

            if ($id) {
                $payment->setId($id);

                if ($payment instanceof Carte) {
                    $query = "INSERT INTO cartebancaires(paiment_id, creditCardNumber) 
                              VALUES(:paiment_id, :creditCardNumber)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([
                        ":paiment_id" => $id,
                        ":creditCardNumber" => $payment->creditCardNumber
                    ]);
                } else if ($payment instanceof PayPal) {
                    $query = "INSERT INTO paypals(paiment_id, paymentEmail, paymentPassword) 
                              VALUES(:paiment_id, :paymentEmail, :paymentPassword)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([
                        ":paiment_id" => $id,
                        ":paymentEmail" => $payment->paymentEmail,
                        ":paymentPassword" => $payment->paymentPassword
                    ]);
                } else {
                    $query = "INSERT INTO virements(paiment_id, rib) 
                              VALUES(:paiment_id, :rib)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([
                        ":paiment_id" => $id,
                        ":rib" => $payment->rib
                    ]);
                }
                return $payment;
            }
        } catch (\Throwable $th) {
            throw new EntityCreationException("Payment creation error: " . $th->getMessage(), 403);
        }
    }

    public function update($payment)
    {
        $query = "UPDATE paiements SET status = :status WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $payment->getId(),
                ":status" => $payment->getStatus()
            ]);

            return true;
        } catch (\Throwable $th) {
            throw new EntityCreationException("Payment update error: " . $th->getMessage(), 500);
        }
    }

    public function findByClient($clientId)
    {
        $query = "SELECT p.id, p.montant, p.status, p.date_paiment, p.commande_id,
                         v.rib, 
                         cb.creditCardNumber, 
                         py.paymentEmail
                  FROM paiements p
                  INNER JOIN commandes c ON p.commande_id = c.id
                  LEFT JOIN virements v ON p.id = v.paiment_id
                  LEFT JOIN cartebancaires cb ON p.id = cb.paiment_id
                  LEFT JOIN paypals py ON p.id = py.paiment_id
                  WHERE c.client_id = :client_id
                  ORDER BY p.date_paiment DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\Throwable $th) {
            throw new EntitySearchException("Error fetching payments: " . $th->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        $this->findById($id);

        $query = "DELETE FROM paiements WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([":id" => $id]);
            return true;
        } catch (\Throwable $th) {
            throw new EntityCreationException("Impossible de supprimer ce paiement. Erreur: " . $th->getMessage(), 503);
        }
    }
}
