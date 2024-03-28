<?php
// <!-- switch case get, post, delete -->
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");


require_once 'model.php';

$request_method = $_SERVER["REQUEST_METHOD"];
$request_uri = $_SERVER["REQUEST_URI"];
// Diviser l'URL en segments en utilisant le délimiteur "/"
$segments = explode('/', $request_uri);

// Récupérer le dernier segment qui devrait être le nom de la route
$route_name = end($segments);


// var_dump($request_uri);


switch ($request_method) {

    case 'OPTIONS':
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Headers: Content-Type");
        exit();
    // break;



    case 'GET':
        switch ($route_name) {
            // case '/reservations':
            //     if (isset ($_GET['id'])) {
            //         $id = intval($_GET['id']);
            //         $result = getOneReservation($id);
            //         header('Content-Type: application/json');
            //         echo json_encode($result);
            //     } else {
            //         $result = getAllReservations();
            //         header('Content-Type: application/json');
            //         echo json_encode($result);
            //     }
            //     break;

            case '/inscription':
                $result = getUsers();
                header('Content-Type: application/json');
                echo json_encode($result);
                break;

            case '/connexion':
                // Vérifiez d'abord que l'utilisateur est connecté en vérifiant s'il y a des données de session
                session_start();
                if (isset($_SESSION['email'])) {
                    // Utilisateur connecté, récupérez ses informations à partir de la base de données
                    $email = $_SESSION['email'];
                    $result = checkUser($email); // Vous devez implémenter cette fonction pour récupérer les informations de l'utilisateur par e-mail
                    if ($result) {
                        // Utilisateur trouvé, renvoyez ses informations au format JSON
                        header('Content-Type: application/json');
                        echo json_encode($result);
                    } else {
                        // Utilisateur non trouvé, renvoyez un message d'erreur au format JSON
                        http_response_code(404);
                        echo json_encode(array("message" => "Utilisateur introuvable"));
                    }
                } else {
                    // Utilisateur non connecté, renvoyez un message d'erreur au format JSON
                    http_response_code(401);
                    echo json_encode(array("message" => "Vous devez être connecté pour accéder à cette ressource"));
                }
                break;

            case 'reservations':
                $reservations = getAllReservations();
                if ($reservations) {
                    // Envoyer une réponse JSON avec les réservations
                    http_response_code(200);
                    echo json_encode($reservations);
                } else {
                    // Aucune réservation trouvée, renvoyer un tableau vide
                    http_response_code(200);
                    echo json_encode(array());
                }
                break;

            case 'utilisateurs':
                $users = getUsers();

                if ($users) {
                    http_response_code(200);
                    echo json_encode($users);
                }else{
                    http_response_code(200);
                    echo json_encode(array());
                }
                break;

            default:
                // header("HTTP/1.0 404 Not Found");
                // echo json_encode(array("message" => "Page non trouvée."));
                $result = getUsers();
                header('Content-Type: application/json');
                echo json_encode($result);
                break;
        }
        break;



    case 'POST':
        // var_dump($_POST);
        // var_dump($request_uri);
        // var_dump($route_name);

        switch ($route_name) {
            case 'inscription':

                $donnees = json_decode(file_get_contents('php://input'), true);

                // var_dump($donnees);

                if ($donnees === null) {
                    http_response_code(400);
                    echo json_encode(array("message" => "Les données JSON sont invalides."));
                    exit();
                }


                // Vérifier et ajouter l'utilisateur
                if (isset($donnees['nom_user'], $donnees['prenom_user'], $donnees['numero'], $donnees['email'], $donnees['age'], $donnees['mdp1'])) {
                    $nom_user = $donnees['nom_user'];
                    $prenom_user = $donnees['prenom_user'];
                    $numero = $donnees['numero'];
                    $email = $donnees['email'];
                    $age = $donnees['age'];
                    $mdp1 = $donnees['mdp1'];
                    addOneUser($nom_user, $prenom_user, $mdp1, $numero, $age, $email);
                    // var_dump($donnees);
                    http_response_code(201);
                    echo json_encode(array("message" => "Utilisateur ajouté avec succès."));
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Certains champs requis sont manquants."));
                }

                break;

            case 'connexion':
                $donnees = json_decode(file_get_contents('php://input'), true);

                var_dump($route_name);

                if ($donnees === null) {
                    http_response_code(400);
                    echo json_encode(array("message" => "Aucune donnée n'a été saisi."));
                    exit();
                }

                // Vérifier et connecter l'utilisateur
                if (isset($donnees['email'], $donnees['mdp'])) {
                    $email = $donnees['email'];
                    $mdp = $donnees['mdp'];
                    checkUserLogin($email, $mdp);
                    var_dump($donnees);
                    http_response_code(201);
                    echo json_encode(array("message" => "Utilisateur connecté avec succès."));
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Votre mdp ou login n'est pas correct."));
                }

                break;



            case 'commenter':
                $donnees = json_decode(file_get_contents('php://input'), true);

                // var_dump($route_name);
                var_dump($donnees);

                if ($donnees === null) {
                    http_response_code(400);
                    echo json_encode(array("message" => "Aucune donnée n'a été saisi."));
                    exit();
                }

                // Vérifier et connecter l'utilisateur
                if (isset($donnees['commentaire'], $donnees['id_user'])) {
                    $commentaire = $donnees['commentaire'];
                    $id_user = $donnees['id_user'];
                    addComment($commentaire, $id_user);
                    var_dump($donnees);
                    http_response_code(201);
                    echo json_encode(array("message" => "Commentaire ajouté avec succès."));
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Votre commentaire n'a pas été ajouté."));
                }
                break;

            case 'reserver':
                $donnees = json_decode(file_get_contents('php://input'), true);

                // var_dump($donnees);

                if ($donnees === null) {
                    http_response_code(400);
                    echo json_encode(array("message" => "Aucune donnée n'a été saisi."));
                    exit();
                }

                // Vérifier et ajouter la réservation
                if (isset($donnees)) {
                    $nom_client = $donnees['nom_client'];
                    $prenom_client = $donnees['prenom_client'];
                    $email_client = $donnees['email_client'];
                    $numero_client = $donnees['numero_client'];
                    $date_choisi = $donnees['date_choisi'];
                    $horaire_choisi = $donnees['horaire_choisi'];
                    $prix = $donnees['prix_tota'];
                    $nbr_billets = $donnees['nbr_billets'];

                    addReservation($nom_client, $prenom_client, $email_client, $numero_client, $date_choisi, $horaire_choisi, $prix, $nbr_billets);


                    http_response_code(201);
                    echo json_encode(array("message" => "Réservation ajoutée avec succès."));
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Votre réservation n'a pas été ajoutée."));
                }
                break;

            case 'booking':
                $donnees = json_decode(file_get_contents('php://input'), true);

                // var_dump($donnees);

                if ($donnees === null) {
                    http_response_code(400);
                    echo json_encode(array("message" => "Aucune donnée n'a été saisi."));
                    exit();
                }

                // Vérifier et ajouter la réservation
                if (isset($donnees)) {
                    $nom_client = $donnees['nom_client'];
                    $prenom_client = $donnees['prenom_client'];
                    $email_client = $donnees['email_client'];
                    $numero_client = $donnees['numero_client'];
                    $date_choisi = $donnees['date_choisi'];
                    $horaire_choisi = $donnees['horaire_choisi'];
                    $prix = $donnees['prix_tota'];
                    $nbr_billets = $donnees['nbr_billets'];

                    addReservation($nom_client, $prenom_client, $email_client, $numero_client, $date_choisi, $horaire_choisi, $prix, $nbr_billets);


                    http_response_code(201);
                    echo json_encode(array("message" => "Réservation ajoutée avec succès."));
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Votre réservation n'a pas été ajoutée."));
                }
                break;



            default:
                header("HTTP/1.0 404 Not Found");
                echo json_encode(array("message" => "Page non trouvée"));
                break;
        }
        break;

    // case 'DELETE':
    //     $id = intval($_GET['id']);
    //     deleteReservation($id);
    //     break;

    default:
        header("HTTP/1.0 405 Method Not Allowed");
        echo json_encode(array("message" => "Méthode non autorisée."));
        break;


    case 'DELETE':
        switch ($route_name) {
            case '/supprimer_reservation':
                // Récupérer les données JSON de la requête
                $donnees = json_decode(file_get_contents('php://input'), true);

                // Vérifier si les données sont valides et complètes
                if ($donnees === null || !isset($donnees['id_reservation'])) {
                    http_response_code(400);
                    header('Content-Type: application/json');
                    echo json_encode(array("message" => "Les données sont incorrectes ou incomplètes."));
                    exit();
                }

                // Supprimer la réservation avec l'ID spécifié
                $success = deleteReservation($donnees['id_reservation']);

                // Vérifier si la suppression a réussi
                if ($success) {
                    // Envoi d'une réponse réussie
                    http_response_code(200);
                    header('Content-Type: application/json');
                    echo json_encode(array("message" => "La réservation a été supprimée avec succès."));
                } else {
                    // Envoi d'une réponse indiquant que la réservation n'a pas été trouvée
                    http_response_code(404);
                    header('Content-Type: application/json');
                    echo json_encode(array("message" => "La réservation n'a pas été trouvée."));
                }
                break;

            default:
                header("HTTP/1.0 404 Not Found");
                echo json_encode(array("message" => "Page non trouvée"));
                break;
        }
        break;

}
;