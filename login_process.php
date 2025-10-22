<?php
session_start();
require 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: authentification.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Veuillez remplir tous les champs.';
    header('Location: authentification.php');
    exit;
}

// Requête sur la table 'users'
$sql = 'SELECT * FROM users WHERE nom = :username';
$stmt = $conn->prepare($sql);
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['mot_de_passe'])) {
    $_SESSION['login_error'] = 'Identifiants invalides.';
    header('Location: authentification.php');
    exit;
}

// Connexion réussie
$_SESSION['user_id']   = $user['id'];
$_SESSION['username']  = $user['nom'];
$_SESSION['email']     = $user['email'];
$_SESSION['user_role'] = $user['role']; // important pour la redirection par rôle


// Redirection selon le rôle
switch ($user['role']) {
    case 'admin':
        header('Location: dashboards/admin_dashboard.php');
        break;
    case 'encadrant':
        header('Location: dashboards/encadrant_dashboard.php');
        break;
    case 'etudiant':
        header('Location: dashboards/etudiant_dashboard.php');
        break;
    default:
        $_SESSION['login_error'] = 'Rôle utilisateur inconnu.';
        header('Location: authentification.php');
}
session_regenerate_id();
exit;
