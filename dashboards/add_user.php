<?php
session_start();

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../authentification.php");
    exit;
}

require '../connexion.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $role    = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($nom === '' || $email === '' || $password === '' || $role === '') {
        $errors[] = "Tous les champs sont obligatoires.";
    }

    if (!in_array($role, ['admin', 'encadrant', 'etudiant'])) {
        $errors[] = "Rôle invalide.";
    }

    if (empty($errors)) {
        // Hasher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $hashedPassword, $role]);

        $success = "Utilisateur ajouté avec succès.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur</title>
    <link rel="stylesheet" href="add.css">
</head>
<body>
    <h1>➕ Ajouter un utilisateur</h1>
    <a href="manage_accounts.php">← Retour à la gestion des comptes</a><br><br>

    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <?php foreach ($errors as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php elseif ($success): ?>
        <div style="color: green;"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" action="add_user.php">
        <label>Nom complet :</label><br>
        <input type="text" name="nom" required><br><br>

        <label>Email :</label><br>
        <input type="email" name="email" required><br><br>

        <label>Mot de passe :</label><br>
        <input type="password" name="password" required><br><br>

        <label>Rôle :</label><br>
        <select name="role" required>
            <option value="">-- Choisir un rôle --</option>
            <option value="admin">Admin</option>
            <option value="encadrant">Encadrant</option>
            <option value="etudiant">Étudiant</option>
        </select><br><br>

        <button type="submit">Ajouter</button>
    </form>
</body>
</html>
