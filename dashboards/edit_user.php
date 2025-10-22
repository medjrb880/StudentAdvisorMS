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

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    echo "Utilisateur introuvable.";
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role  = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($nom === '' || $email === '' || $role === '') {
        $errors[] = "Tous les champs obligatoires doivent être remplis.";
    }

    if (!in_array($role, ['admin', 'encadrant', 'etudiant'])) {
        $errors[] = "Rôle invalide.";
    }

    if (empty($errors)) {
        // Mettre à jour avec ou sans mot de passe
        if ($password !== '') {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET nom = ?, email = ?, role = ?, mot_de_passe = ? WHERE id = ?");
            $stmt->execute([$nom, $email, $role, $hashedPassword, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET nom = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$nom, $email, $role, $id]);
        }

        $success = "Mise à jour réussie.";
        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un utilisateur</title>
    <link rel="stylesheet" href="edit.css">
</head>
<body>
    <h1>✏️ Modifier un utilisateur</h1>
    <a href="manage_accounts.php">← Retour</a><br><br>

    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <?php foreach ($errors as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php elseif ($success): ?>
        <div style="color: green;"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Nom complet :</label><br>
        <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required><br><br>

        <label>Email :</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

        <label>Mot de passe (laisser vide pour ne pas changer) :</label><br>
        <input type="password" name="password"><br><br>

        <label>Rôle :</label><br>
        <select name="role" required>
            <option value="">-- Choisir un rôle --</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="encadrant" <?= $user['role'] === 'encadrant' ? 'selected' : '' ?>>Encadrant</option>
            <option value="etudiant" <?= $user['role'] === 'etudiant' ? 'selected' : '' ?>>Étudiant</option>
        </select><br><br>

        <button type="submit">Mettre à jour</button>
    </form>
</body>
</html>
