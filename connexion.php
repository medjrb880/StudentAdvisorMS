<?php
$host = 'localhost';      // ou l'IP de ton serveur de base de données
$dbname = 'gestion_encadrement';
$username = 'root';       // ou ton utilisateur MySQL
$password = '';           // le mot de passe associé à ton utilisateur MySQL

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Configuration pour gérer les erreurs
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
