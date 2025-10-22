<?php
session_start();
require '../connexion.php';  // Adjust the path to your connexion.php file if needed

// Fetch all students from the database
$sql = 'SELECT * FROM users WHERE role = "etudiant"';  // Modify this query if necessary
$stmt = $conn->prepare($sql);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Fetch all students as an associative array

// Check if there are any students
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administrateur</title>
    <link rel="stylesheet" href="admin.css">  <!-- Adjust the path if necessary -->
</head>
<body>
    <h1>Bienvenue, Administrateur</h1>

    <nav>
    <form action="run_auto_assignment.php" method="post">
    <button type="submit">Démarrer le traitement d’affectation automatique</button>
</form>

        <ul>
            <li><a href="list_students.php"> fiche des étudiants</a></li>
            <li><a href="manage_accounts.php">Gérer les comptes</a></li>
            <li><a href="affectations.php">Voir les affectations</a></li>
            <li><a href="../logout.php">Déconnexion</a></li>
        </ul>
    </nav>

    <h2>Liste des étudiants</h2>
    <?php
    if (!empty($students)) {
        echo '<ul>';
        foreach ($students as $student) {
            echo '<li>' . htmlspecialchars($student['nom']) . ' - ' . htmlspecialchars($student['email']) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Aucun étudiant trouvé.</p>';
    }
    ?>
</body>
</html>
