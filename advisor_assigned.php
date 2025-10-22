<?php
session_start();
require 'connexion.php';

// Ensure the student is logged in
if (!isset($_SESSION['client_id'])) {
    header('Location: authentification.php');
    exit;
}

$student_id = $_SESSION['client_id'];

// Query to check if an advisor is assigned
$sql = 'SELECT a.*, e.nom AS advisor_name, e.prenom AS advisor_firstname, e.email AS advisor_email
        FROM affectations a
        JOIN encadrants e ON a.encadrant_id = e.id
        WHERE a.student_id = :student_id AND a.status = "validé"';
$stmt = $conn->prepare($sql);
$stmt->execute([':student_id' => $student_id]);
$assignment = $stmt->fetch();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Encadrant</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <?php if ($assignment): ?>
            <h1>Votre encadrant a été affecté</h1>
            <p><strong>Nom de l'encadrant :</strong> <?= htmlspecialchars($assignment['advisor_firstname'] . ' ' . $assignment['advisor_name']); ?></p>
            <p><strong>Email de l'encadrant :</strong> <?= htmlspecialchars($assignment['advisor_email']); ?></p>
        <?php else: ?>
            <h1>Vous n'avez pas encore d'encadrant affecté</h1>
            <p>Votre affectation sera disponible une fois validée par le chef de département.</p>
        <?php endif; ?>
    </div>
</body>
</html>
