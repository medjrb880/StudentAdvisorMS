<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header('Location: ../authentification.php');
    exit;
}

require '../connexion.php';
// Redirect to assigned encadrant view if preferences were already submitted
$etudiant_id = $_SESSION['user_id'];

$check_pref = $conn->prepare("SELECT * FROM preferences WHERE etudiant_id = ?");
$check_pref->execute([$etudiant_id]);

if ($check_pref->fetch()) {
    header("Location: mon_encadrant.php");
    exit;
}

$etudiant_id = $_SESSION['user_id'];

// Récupérer tous les encadrants
$encadrants = $conn->query("SELECT id, nom, prenom FROM users WHERE role = 'encadrant'")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer l'encadrant affecté s'il existe
$sql = "SELECT u.nom AS encadrant_nom, u.prenom AS encadrant_prenom 
        FROM affectations a
        JOIN users u ON a.encadrant_id = u.id
        WHERE a.etudiant_id = :etudiant_id AND a.valide_par_chef = 1";
$stmt = $conn->prepare($sql);
$stmt->execute([':etudiant_id' => $etudiant_id]);
$affectation = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Étudiant</title>
    <link rel="stylesheet" href="etudiant.css">
    <script>
        // JavaScript validation to check if 3 choices are selected, no duplicates, and all choices are made
        function validateForm() {
            let choix1 = document.forms["choixForm"]["choix1"].value;
            let choix2 = document.forms["choixForm"]["choix2"].value;
            let choix3 = document.forms["choixForm"]["choix3"].value;

            // Check if at least 3 selections are made or none
            if ((choix1 == "" && choix2 == "" && choix3 == "") || (choix1 != "" && choix2 != "" && choix3 != "")) {
                // Check if any of the selections are duplicates
                if (choix1 && choix2 && choix3 && (choix1 === choix2 || choix1 === choix3 || choix2 === choix3)) {
                    alert("Vous ne pouvez pas sélectionner deux encadrants identiques.");
                    return false;
                }
                return true; // Proceed if valid selection
            } else {
                alert("Vous devez soit sélectionner 3 encadrants.");
                return false; // Prevent form submission
            }
        }
    </script>
</head>
<body>
    <h1>Bienvenue, étudiant</h1>
    <p>Ici, vous pouvez gérer vos choix d’encadrants et consulter votre affectation.</p>

    <h2>🎓 Saisir vos choix d’encadrants</h2>
    <form name="choixForm" action="submit_choix.php" method="post" onsubmit="return validateForm()">
        <label for="choix1">Choix 1 :</label>
        <select name="choix1" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($encadrants as $enc): ?>
                <option value="<?= $enc['id'] ?>"><?= htmlspecialchars($enc['prenom'] . ' ' . $enc['nom']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="choix2">Choix 2 :</label>
        <select name="choix2">
            <option value="">-- Facultatif --</option>
            <?php foreach ($encadrants as $enc): ?>
                <option value="<?= $enc['id'] ?>"><?= htmlspecialchars($enc['prenom'] . ' ' . $enc['nom']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="choix3">Choix 3 :</label>
        <select name="choix3">
            <option value="">-- Facultatif --</option>
            <?php foreach ($encadrants as $enc): ?>
                <option value="<?= $enc['id'] ?>"><?= htmlspecialchars($enc['prenom'] . ' ' . $enc['nom']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <input type="submit" value="Soumettre mes choix">
    </form>

    <h2>📌 Encadrant affecté</h2>
    <?php if ($affectation && $affectation['encadrant_nom']): ?>
        <p><strong>Vous avez été affecté à :</strong> <?= htmlspecialchars($affectation['encadrant_prenom'] . ' ' . $affectation['encadrant_nom']) ?></p>
    <?php else: ?>
        <p>Vous n'avez pas encore été affecté à un encadrant.</p>
    <?php endif; ?>

    <br>
    <a href="profile.php">Voir ton profil</a>
    <a href="../logout.php">Déconnexion</a>

</body>
</html>
