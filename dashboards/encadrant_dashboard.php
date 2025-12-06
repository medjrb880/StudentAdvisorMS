<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'encadrant') {
    header('Location: ../authentification.php');
    exit;
}

require '../connexion.php';

$encadrant_id = $_SESSION['user_id'];

// Récupérer les étudiants affectés à cet encadrant via la table des affectations
$sql = "
SELECT u.id, u.nom, u.prenom, u.numero_inscription, u.parcours
FROM affectations a
JOIN users u ON a.etudiant_id = u.id
WHERE a.encadrant_id = :encadrant_id AND u.role = 'etudiant'
ORDER BY u.nom ASC
";
$stmt = $conn->prepare($sql);
$stmt->execute([':encadrant_id' => $encadrant_id]);
$etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Encadrant</title>
    <link rel="stylesheet" href="encadrant.css">
</head>
<body>
    <h1>🧑‍🏫 Espace Encadrant</h1>
    <p>Bienvenue, <?= htmlspecialchars($_SESSION['username']) ?> !</p>

    <h2>📋 Liste des étudiants affectés</h2>

    <?php if ($etudiants): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Numéro d'inscription</th>
                    <th>Parcours</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $etu): ?>
                    <tr>
                        <td><?= htmlspecialchars($etu['id']) ?></td>
                        <td><?= htmlspecialchars($etu['nom']) ?></td>
                        <td><?= htmlspecialchars($etu['prenom']) ?></td>
                        <td><?= htmlspecialchars($etu['numero_inscription']) ?></td>
                        <td><?= htmlspecialchars($etu['parcours']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun étudiant ne vous est encore affecté.</p>
    <?php endif; ?>

    <br>
    <a href="../logout.php">Déconnexion</a>
</body>
</html>
