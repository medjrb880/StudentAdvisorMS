<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../authentification.php");
    exit;
}

require '../connexion.php';

// Handle form submission to update encadrants and validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['affectations'] as $id => $data) {
        $encadrant_id = $data['encadrant_id'];
        $valide = isset($data['valide']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE affectations SET encadrant_id = :encadrant_id, valide_par_chef = :valide WHERE id = :id");
        $stmt->execute([
            'encadrant_id' => $encadrant_id,
            'valide' => $valide,
            'id' => $id
        ]);
    }
    header("Location: affectations.php?updated=1");
    exit;
}

// Get encadrants list
$encadrants = $conn->query("SELECT id, nom FROM users WHERE role = 'encadrant'")->fetchAll(PDO::FETCH_ASSOC);

// Get current affectations
$sql = "
SELECT 
    a.id AS affectation_id,
    a.encadrant_id,
    a.valide_par_chef,
    e.nom AS etudiant_nom,
    e.prenom AS etudiant_prenom,
    u.nom AS encadrant_nom,
    u.email AS encadrant_email,
    a.date_affectation
FROM affectations a
JOIN etudiants e ON a.etudiant_id = e.id
JOIN users u ON a.encadrant_id = u.id
ORDER BY a.date_affectation DESC
";

$result = $conn->query($sql);
$affectations = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des affectations</title>
    <link rel="stylesheet" href="affectations.css">
</head>
<body>
    <h1>Liste des affectations</h1>
    <a href="admin_dashboard.php">← Retour au tableau de bord</a>
    <?php if (isset($_GET['updated'])): ?>
        <p style="color: green;">Modifications enregistrées avec succès.</p>
    <?php endif; ?>
    <form method="POST">
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Étudiant</th>
                    <th>Encadrant</th>
                    <th>Email Encadrant</th>
                    <th>Date d'affectation</th>
                    <th>Valider</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($affectations): ?>
                    <?php foreach ($affectations as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['affectation_id']) ?></td>
                            <td><?= htmlspecialchars($row['etudiant_nom'] . ' ' . $row['etudiant_prenom']) ?></td>
                            <td>
                                <select name="affectations[<?= $row['affectation_id'] ?>][encadrant_id]">
                                    <?php foreach ($encadrants as $encadrant): ?>
                                        <option value="<?= $encadrant['id'] ?>" <?= $encadrant['id'] == $row['encadrant_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($encadrant['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><?= htmlspecialchars($row['encadrant_email']) ?></td>
                            <td><?= htmlspecialchars($row['date_affectation']) ?></td>
                            <td>
                                <input type="checkbox" name="affectations[<?= $row['affectation_id'] ?>][valide]" <?= $row['valide_par_chef'] ? 'checked' : '' ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">Aucune affectation trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <button type="submit">Enregistrer les modifications</button>
    </form>
</body>
</html>
