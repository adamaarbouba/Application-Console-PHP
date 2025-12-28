<?php


require_once "./exception/ValidationException.php";
require_once "./exception/EntityCreationException.php";
require_once "./exception/ServerErrorException.php";
require_once "./exception/EntitySearchException.php";
require_once "./entity/Client.php";
require_once "./entity/Commande.php";
require_once "./entity/Virement.php";
require_once "./entity/PayPal.php";
require_once "./entity/Carte.php";
require_once "./repository/ClientRepository.php";
require_once "./repository/CommandeRepository.php";
require_once "./repository/PaymentRepository.php";

class ConsolePaymentApp
{


    private $clientRepository;
    private $commandeRepository;
    private $paymentRepository;


    public function __construct()
    {
        $this->clientRepository = new ClientRepository();
        $this->commandeRepository = new CommandeRepository();
        $this->paymentRepository = new PaymentRepository();
    }


    public function run()
    {

        echo "╔═══════════════════════════════════════════════════════════╗\n";
        echo "║     SYSTÈME DE GESTION DE PAIEMENT - CONSOLE APP          ║\n";
        echo "╚═══════════════════════════════════════════════════════════╝\n\n";


        try {

            while (true) {
                $this->displayMenu();
                $choix = $this->readUserInput("\nVeuiller entrez votre choix svp: \n");

                match ($choix) {
                    "1" => $this->createClient(),
                    "2" => $this->listAllClients(),
                    "3" => $this->createCommande(),
                    "4" => $this->listAllCommandes(),
                    "5" => $this->createPayment(),
                    "6" => $this->ListPaiments(),
                    "7" => $this->DeletePayment(),
                    "0" => $this->exitApp()
                };
            }
        } catch (ValidationException $e) {
            echo "\n \n Code: " . $e->getCode() . " Message:" . $e->getMessage();
            // $this->run();
        } catch (EntityCreationException $e) {
            echo "\n \n Code: " . $e->getCode() . " Message:" . $e->getMessage();
            // $this->run();
        } catch (ServerErrorException $e) {
            echo "\n \n Code: " . $e->getCode() . " Message:" . $e->getMessage();
            // $this->run();
        } catch (EntitySearchException $e) {
            echo "\n \n Code: " . $e->getCode() . " Message:" . $e->getMessage();
            // $this->run();
        }
    }


    public function displayMenu()
    {
        echo "\n\n";
        echo "┌─────────────────────────────────────────────────────────┐\n";
        echo "│ MENU PRINCIPAL                                          │\n";
        echo "├─────────────────────────────────────────────────────────┤\n";
        echo "│ 1. Créer un client                                      │\n";
        echo "│ 2. Lister les clients                                   │\n";
        echo "│ 3. Créer une commande                                   │\n";
        echo "│ 4. Lister les commandes                                 │\n";
        echo "│ 5. Créer un paiement                                    │\n";
        echo "│ 6. List Paiments                                        │\n";
        echo "│ 7. Delete Payment                                       │\n";
        echo "│ 0. Exit App                                             │\n";
        echo "└─────────────────────────────────────────────────────────┘\n";
    }


    public function readUserInput($prompt)
    {
        $input = readline($prompt);
        $input = trim($input);

        //  echo gettype($input), "\n";

        if (empty($input) && $input != 0) {
            throw new ValidationException("ERROR vient de:  " . $prompt, 100);
        }
        return $input;
    }


    public function createPayment()
    {
        echo "\n Demande de création d'un Payment: \n";

        $this->listAllCommandes();
        $id = $this->readUserInput("Veuillez choisir l'id de la commande: ");

        $commande = $this->commandeRepository->findById($id);

        echo "\n\n";
        echo "┌────────────────────────────┐\n";
        echo "│ Payment Menu               │\n";
        echo "├────────────────────────────┤\n";
        echo "│ 1. Virement                │\n";
        echo "│ 2. Carte                   │\n";
        echo "│ 3. PayPal                  │\n";
        echo "└────────────────────────────┘\n";

        $choix = $this->readUserInput("\n Veuillez choisir un mode de payment: ");

        $payment = match ($choix) {
            "1" => $this->createVirementInstance($commande),
            "2" => $this->createCarteInstance($commande),
            "3" => $this->createPayPalInstance($commande)
        };

        $payment->setCommande($commande);

        // 1. Process the payment (Status becomes "Paid")
        $payment->pay();

        // 2. *** NEW FIX: Update the Commande status to match the Payment ***
        if ($payment->getStatus() === "Paid") {
            $commande->setStatus("Paid");
        }
        $payment = $this->paymentRepository->create($payment);
        $this->commandeRepository->update($commande);
    }

    public function createCommande()
    {
        echo "\n Demande de création d'une commande: \n";

        $this->listAllClients();
        $id = $this->readUserInput("Veuillez choisir l'id du client: ");

        $client = $this->clientRepository->findById($id);
        $montantTotal = $this->readUserInput("\n Veuillez entrez le montant total de la commande: ");

        $commande  = new Commande($montantTotal);
        $commande->setClient($client);

        return $this->commandeRepository->create($commande);
    }

    public function createClient()
    {
        echo "\n Demande de création d'un client: \n";

        $name = $this->readUserInput(" Entrez le nom du client: ");
        $email = $this->readUserInput("\n Entrez l'email du client: ");

        $client  = new Client($name, $email);

        // passer ce client vers la couche de la base de donnée.

        return $this->clientRepository->create($client);
    }

    public function listAllClients()
    {

        $clients = $this->clientRepository->findAll();

        if (empty($clients)) {

            echo "\n Aucun client n'est présent a ce moment !\n";
            return;
        }

        printf("%-5s  %-30s  %-50s\n", "ID", "Name", "Email");

        foreach ($clients as $client) {
            printf("%-5s  %-30s  %-50s \n", $client->id, $client->name, $client->email);
        }
    }


    public function listAllCommandes()
    {

        $commandes = $this->commandeRepository->findAll();

        if (empty($commandes)) {
            echo "\n Aucune commande n'est présente à ce moment !\n";
            return;
        }

        printf("%-5s  %-30s  %-10s  %-30s  \n", "ID", "Client Name", "Montant Total", "Status");

        foreach ($commandes as $cmd) {
            printf("%-5s  %-30s  %-10s  %-30s  \n", $cmd->id, $cmd->client->name, $cmd->montantTotal, $cmd->status);
        }
    }

    public function createVirementInstance($commande)
    {
        $rib = $this->readUserInput("\n Entrez le rib: ");
        $payment = new Virement($commande->montantTotal, $rib);
        return $payment;
    }



    public function createCarteInstance($commande)
    {
        $numeroCarte = $this->readUserInput("\n Entrez le numéro de la carte: ");
        $payment = new Carte($commande->montantTotal, $numeroCarte);
        return $payment;
    }

    public function createPayPalInstance($commande)
    {
        $email = $this->readUserInput("\n Entrez l'email: ");
        $password = $this->readUserInput("\n Entrez le password: ");
        $payment = new PayPal($commande->montantTotal, $email, $password);
        return $payment;
    }
    public function ListPaiments()
    {
        echo "┌─────────────────────────────────────────────────────────┐\n";
        echo "│ History                                                 │\n";
        echo "└─────────────────────────────────────────────────────────┘\n";



        $this->listAllClients();

        $id = $this->readUserInput("\nEntrez l'ID du Client pour voir ses paiements : ");

        try {
            $payments = $this->paymentRepository->findByClient($id);

            if (empty($payments)) {
                echo "\nAucun paiement trouvé pour ce client.\n";
                return;
            }

            printf(
                "\n%-5s %-10s %-10s %-12s %-20s %-25s\n",
                "ID",
                "Cmd ID",
                "Montant",
                "Status",
                "Date",
                "Type"
            );
            echo "\n";

            foreach ($payments as $p) {
                $details = "Inconnu";
                if (!empty($p->rib)) {
                    $details = "Virement (RIB: " . $p->rib . ")";
                } elseif (!empty($p->creditCardNumber)) {
                    $details = "Carte (N°: " . $p->creditCardNumber . ")";
                } elseif (!empty($p->paymentEmail)) {
                    $details = "PayPal (" . $p->paymentEmail . ")";
                }

                printf(
                    "%-5s %-10s %-10s %-12s %-20s %-25s\n",
                    $p->id,
                    $p->commande_id,
                    $p->montant,
                    $p->status,
                    $p->date_paiment,
                    $details
                );
            }
            echo "\n";
        } catch (Exception $e) {
            echo "\nERREUR " . $e->getMessage() . "\n";
        }
    }
    public function DeletePayment()
    {
        $this->ListPaiments();
        $id = $this->readUserInput("Entrez l'ID du paiement à supprimer: ");

        try {
            $this->paymentRepository->delete($id);

            echo "\nSUCCESS $id et Supprime\n";
        } catch (EntitySearchException $e) {
            echo "\nERREUR Paiement introuvable id : $id.\n";
        } catch (Exception $e) {
            echo "\nERREUR " . $e->getMessage() . "\n";
        }
    }

    public function exitApp()
    {
        echo "\n exiting app ..... \n";
        exit(0);
    }
}
