<?php
session_start();
require '../connexion.php';

// Redirection si l'utilisateur n'est pas un étudiant
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'etudiant') {
    header('Location: ../authentification.php');
    exit;
}

$etudiant_id = $_SESSION['user_id'];

// Empêcher un étudiant de soumettre plusieurs fois
$check = $conn->prepare("SELECT * FROM preferences WHERE etudiant_id = ?");
$check->execute([$etudiant_id]);
$already_submitted = $check->fetch();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_submitted) {
    $choix1 = $_POST['choix1'];
    $choix2 = $_POST['choix2'];
    $choix3 = $_POST['choix3'];

    $stmt = $conn->prepare("INSERT INTO preferences (etudiant_id, choix1_id, choix2_id, choix3_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$etudiant_id, $choix1, $choix2, $choix3]);

    header("Location: mon_encadrant.php");
    exit;
}

// Récupérer les encadrants
$encadrants = $conn->query("SELECT id, nom, prenom FROM users WHERE role = 'encadrant'")->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choix d'encadrants</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
    <h2>Choix d'encadrants</h2>

    <?php if ($already_submitted): ?>
        <p>Vous avez déjà soumis vos choix.</p>
    <?php else: ?>
        <form method="POST">
            <label for="choix1">Choix 1 :</label>
            <select name="choix1" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($encadrants as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></option>
                <?php endforeach; ?>
            </select><br>

            <label for="choix2">Choix 2 :</label>
            <select name="choix2">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($encadrants as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></option>
                <?php endforeach; ?>
            </select><br>

            <label for="choix3">Choix 3 :</label>
            <select name="choix3">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($encadrants as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <button type="submit">Soumettre mes choix</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
