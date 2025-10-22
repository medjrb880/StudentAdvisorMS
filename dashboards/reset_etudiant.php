<?php
session_start();
require '../connexion.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header('Location: ../authentification.php');
    exit;
}

$etudiant_id = $_SESSION['user_id'];

try {
    // Supprimer les préférences de l'étudiant
    $conn->prepare("DELETE FROM preferences WHERE etudiant_id = ?")->execute([$etudiant_id]);

    // Supprimer l'affectation s'il y en a une
    $conn->prepare("DELETE FROM affectations WHERE etudiant_id = ?")->execute([$etudiant_id]);

    header('Location: etudiant_dashboard.php?reset=1');
    exit;

} catch (PDOException $e) {
    echo "Erreur lors de la réinitialisation : " . $e->getMessage();
}
