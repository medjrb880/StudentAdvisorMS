<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../authentification.php");
    exit;
}

require '../connexion.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: manage_accounts.php");
    exit;
}

// On s'assure que l'admin ne peut pas se supprimer lui-même
if ($_SESSION['user_id'] == $id) {
    $_SESSION['delete_error'] = "Vous ne pouvez pas supprimer votre propre compte.";
    header("Location: manage_accounts.php");
    exit;
}

// Vérifier si l'utilisateur existe
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['delete_error'] = "Utilisateur introuvable.";
    header("Location: manage_accounts.php");
    exit;
}

// Supprimer l'utilisateur
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['delete_success'] = "Utilisateur supprimé avec succès.";
header("Location: manage_accounts.php");
exit;
