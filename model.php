<?php
function dbConnect() //Fonction qui permet de se connecter à la bdd
{
    $db = new PDO('mysql:host=localhost;dbname=morisot;port=3306;charset=utf8', 'root', '');
    return $db;
}

function deconnect() //Fonction qui permet de déconnecter l'utilisateur en détruisant sa session
{
    session_destroy();
    $_SESSION = array();
}

function addOneUser($nom_user, $prenom_user, $mdp, $numero, $age, $email) //Fonction qui permet d'ajouter un utilisateur à la bdd
{
    $db = dbConnect();
    // $mdp = password_hash(($_POST['mdp1']), PASSWORD_DEFAULT);
    $mdp_hache = password_hash($mdp, PASSWORD_DEFAULT);

    $requete = "INSERT INTO utilisateurs (nom, prenom, mdp, numero, age, mail) VALUES (:nom, :prenom, :mdp, :numero, :age, :mail)";
    $stmt = $db->prepare($requete);
    $stmt->bindValue(":nom", $nom_user, PDO::PARAM_STR);
    $stmt->bindValue(":prenom", $prenom_user, PDO::PARAM_STR);
    $stmt->bindValue(":mdp", $mdp_hache, PDO::PARAM_STR);
    $stmt->bindValue(":numero", $numero, PDO::PARAM_INT);
    $stmt->bindValue(":age", $age, PDO::PARAM_INT);
    $stmt->bindValue(":mail", $email, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt;
}

function checkUser($email) //Fonction qui permet de vérifier si l'utilisateur existe déjà dans la bdd
{
    $db = dbConnect();
    $requete = "SELECT * FROM utilisateurs WHERE mail = :mail";
    $stmt = $db->prepare($requete);
    $stmt->bindValue(":mail", $email, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch();
}

function checkUserLogin($email, $mdp) //Fonction qui permet de vérifier si l'utilisateur existe déjà dans la bdd
{
    $db = dbConnect();
    $requete = "SELECT * FROM utilisateurs WHERE mail = :mail";
    $stmt = $db->prepare($requete);
    $stmt->bindValue(":mail", $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();
    if (password_verify($mdp, $user['mdp'])) {
        return $user;
    } else {
        return false;
    }
}

function getUsers() //Fonction qui permet de récupérer tous les utilisateurs de la bdd
{
    $db = dbConnect();
    $requete = "SELECT * FROM utilisateurs";
    $stmt = $db->prepare($requete);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getUser($id) //Fonction qui permet de récupérer un utilisateur de la bdd
{
    $db = dbConnect();
    $requete = "SELECT * FROM utilisateurs WHERE id_user = :id";
    $stmt = $db->prepare($requete);
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

function deleteUser($id) //Fonction qui permet de supprimer un utilisateur de la bdd
{
    $db = dbConnect();
    $requete = "DELETE FROM utilisateurs WHERE id_user = :id";
    $stmt = $db->prepare($requete);
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

function addReservation($nom, $prenom, $email, $tel, $date, $horaire, $prix, $quantite) //Fonction qui permet d'ajouter une réservation à la bdd
{
    $db = dbConnect();
    $requete = "INSERT INTO reservation (nom_client, prenom_client, email_client, numero_client, date_choisi, horaire_choisi, prix_tota, nbr_billets) VALUES (:nom, :prenom, :mail, :numero, :date, :horaire, :prix_tota, :quantite)";
    $stmt = $db->prepare($requete);
    $stmt->bindValue(":nom", $nom, PDO::PARAM_STR);
    $stmt->bindValue(":prenom", $prenom, PDO::PARAM_STR);
    $stmt->bindValue(":mail", $email, PDO::PARAM_STR);
    $stmt->bindValue(":numero", $tel, PDO::PARAM_INT);
    $stmt->bindValue(":date", $date, PDO::PARAM_STR);
    $stmt->bindValue(":horaire", $horaire, PDO::PARAM_STR);
    $stmt->bindValue(":prix_tota", $prix, PDO::PARAM_INT);
    $stmt->bindValue(":quantite", $quantite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

function deleteReservation($id) //Fonction qui permet de supprimer une réservation de la bdd
{
    $db = dbConnect();
    $requete = "DELETE FROM reservation WHERE id_resa = :id";
    $stmt = $db->prepare($requete);
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

function getOneReservation($id) //Fonction qui permet de récupérer une réservation de la bdd
{
    $db = dbConnect();
    $requete = "SELECT * FROM reservation WHERE id_resa = :id";
    $stmt = $db->prepare($requete);
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

function getLastReservation()
{
    $db = dbConnect();
    $requete = "SELECT * FROM reservation ORDER BY id_resa DESC LIMIT 1";
    $stmt = $db->prepare($requete);
    $stmt->execute();
    return $stmt->fetch();

}

function getReservationByPerson($email) {
    $db = dbConnect();
    $requete = "SELECT * FROM reservation
                INNER JOIN utilisateurs ON reservation.email_client = utilisateurs.mail WHERE email_client = :email ORDER BY date_choisi DESC, horaire_choisi ASC";
    $stmt = $db->prepare($requete);
    $stmt->bindValue(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll();
}



function getAllReservations() //Fonction qui permet de récupérer toutes les réservations de la bdd
{
    $db = dbConnect();
    $requete = "SELECT * FROM reservation ORDER BY date_choisi DESC, horaire_choisi ASC";
    $stmt = $db->prepare($requete);
    $stmt->execute();
    return $stmt->fetchAll();
}

function SendReservation($nom, $prenom, $email, $tel, $date, $horaire, $prix, $quantite) //Fonction qui permet d'envoyer une réservation
{

    $to = $email;
    $mailFrom = "vision@ombreetlumiere.eu";
    
    $fromName = "Équipe Ombre et Lumière";

    $subject = "Réservation exposition Berthe Morisot";

    $headers = "From: $fromName <$mailFrom>\r\n";
    $headers .= "Reply-To: $mailFrom\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
$nom_tronque = strtoupper(substr($nom, 0, 3));

$id = getLastReservation()['id_resa'];

$num_reservation = "Numéro de réservation : " . $id . "#" . $nom_tronque;

        
$message = "Bonjour " . $prenom . " " . $nom . ",\n\nNous vous confirmons votre réservation qui porte le numéro " . $num_reservation . " pour le " . $date . " à " . $horaire . ".\n\nVous avez réservé " . $quantite . " billet(s) pour un montant total de " . $prix . "€.\n\nNous vous remercions pour votre confiance et nous vous attendons avec impatience.\n\nCordialement,\n\nL'équipe du musée Morisot";

    mail($to, $subject, $message, $headers);
}


function addComment($commentaire, $id_user) //Fonction qui permet d'ajouter un commentaire à la bdd
{
    $db = dbConnect();
    $requete = "INSERT INTO commentaires (content, ext_user) VALUES (:commentaire, :ext_user)";
    $stmt = $db->prepare($requete);
    $stmt->bindValue(":commentaire", $commentaire, PDO::PARAM_STR);
    $stmt->bindValue(":ext_user", $id_user, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

function getComments() //Fonction qui permet de récupérer tous les commentaires de la bdd
{
    $db = dbConnect();
    $requete = "SELECT * FROM commentaires ORDER BY id_comment DESC";
    $stmt = $db->prepare($requete);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getComment($id) //Fonction qui permet de récupérer un commentaire de la bdd
{
    $db = dbConnect();
    $requete = "SELECT * FROM commentaires WHERE ext_user = :id";
    $stmt = $db->prepare($requete);
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

//toutes mes fonctions du site