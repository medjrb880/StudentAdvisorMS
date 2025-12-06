<?php
session_start();

// Security: Only allow admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../authentification.php");
    exit;
}

require '../connexion.php'; // DB connection

// Fetch all students (calculating average of 1st and 2nd year)
$sql = "SELECT id, nom, prenom, numero_inscription, parcours, moyenne_1ere_annee, moyenne_2eme_annee 
       FROM users 
       WHERE role = 'etudiant' 
       ORDER BY nom ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des étudiants</title>
    <link rel="stylesheet" href="list.css"> <!-- Optional -->
</head>
<body>
    <h1>Liste des étudiants</h1>
    <a href="admin_dashboard.php">← Retour au tableau de bord</a>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Moyenne 1ère année</th>
                <th>Moyenne 2ème année</th>
                <th>Moyenne Générale</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($students): ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['id']) ?></td>
                        <td><?= htmlspecialchars($student['nom']) ?></td>
                        <td><?= htmlspecialchars($student['prenom']) ?></td>
                        <td><?= htmlspecialchars($student['moyenne_1ere_annee']) ?></td>
                        <td><?= htmlspecialchars($student['moyenne_2eme_annee']) ?></td>
                        <td>
                            <?= htmlspecialchars(
                                round(($student['moyenne_1ere_annee'] + $student['moyenne_2eme_annee']) / 2, 2)
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">Aucun étudiant trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
