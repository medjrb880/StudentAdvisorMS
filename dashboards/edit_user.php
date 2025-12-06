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
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role  = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($nom === '' || $prenom === '' || $email === '' || $role === '') {
        $errors[] = "Tous les champs obligatoires doivent être remplis.";
    }

    if (!in_array($role, ['admin', 'encadrant', 'etudiant'])) {
        $errors[] = "Rôle invalide.";
    }

    if (empty($errors)) {
        // Build dynamic UPDATE query
        $sql = "UPDATE users SET nom = ?, prenom = ?, email = ?, role = ?";
        $params = [$nom, $prenom, $email, $role];
        
        // Handle password update
        if ($password !== '') {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", mot_de_passe = ?";
            $params[] = $hashedPassword;
        }
        
        // Handle role-specific fields
        if ($role === 'etudiant') {
            $numero_inscription = trim($_POST['numero_inscription'] ?? '');
            $parcours = trim($_POST['parcours'] ?? '');
            $moyenne_1 = floatval($_POST['moyenne_1ere_annee'] ?? 0);
            $moyenne_2 = floatval($_POST['moyenne_2eme_annee'] ?? 0);
            
            $sql .= ", numero_inscription = ?, parcours = ?, moyenne_1ere_annee = ?, moyenne_2eme_annee = ?";
            $params[] = $numero_inscription;
            $params[] = $parcours;
            $params[] = $moyenne_1;
            $params[] = $moyenne_2;
        } elseif ($role === 'encadrant') {
            $quota_max = intval($_POST['quota_max'] ?? 5);
            $sql .= ", quota_max = ?";
            $params[] = $quota_max;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

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
        <label>Nom :</label><br>
        <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required><br><br>
        
        <label>Prénom :</label><br>
        <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" required><br><br>

        <label>Email :</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

        <label>Mot de passe (laisser vide pour ne pas changer) :</label><br>
        <input type="password" name="password"><br><br>

        <label>Rôle :</label><br>
        <select name="role" id="role" required onchange="toggleRoleFields()">
            <option value="">-- Choisir un rôle --</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="encadrant" <?= $user['role'] === 'encadrant' ? 'selected' : '' ?>>Encadrant</option>
            <option value="etudiant" <?= $user['role'] === 'etudiant' ? 'selected' : '' ?>>Étudiant</option>
        </select><br><br>
        
        <div id="etudiant-fields" style="display:<?= $user['role'] === 'etudiant' ? 'block' : 'none' ?>;">
            <label>Numéro d'inscription :</label><br>
            <input type="text" name="numero_inscription" value="<?= htmlspecialchars($user['numero_inscription'] ?? '') ?>"><br><br>
            
            <label>Parcours :</label><br>
            <input type="text" name="parcours" value="<?= htmlspecialchars($user['parcours'] ?? '') ?>"><br><br>
            
            <label>Moyenne 1ère année :</label><br>
            <input type="number" step="0.01" name="moyenne_1ere_annee" value="<?= htmlspecialchars($user['moyenne_1ere_annee'] ?? '') ?>"><br><br>
            
            <label>Moyenne 2ème année :</label><br>
            <input type="number" step="0.01" name="moyenne_2eme_annee" value="<?= htmlspecialchars($user['moyenne_2eme_annee'] ?? '') ?>"><br><br>
        </div>
        
        <div id="encadrant-fields" style="display:<?= $user['role'] === 'encadrant' ? 'block' : 'none' ?>;">
            <label>Quota maximum d'étudiants :</label><br>
            <input type="number" name="quota_max" value="<?= htmlspecialchars($user['quota_max'] ?? 5) ?>"><br><br>
        </div>

        <button type="submit">Mettre à jour</button>
    </form>
    
    <script>
    function toggleRoleFields() {
        const role = document.getElementById('role').value;
        document.getElementById('etudiant-fields').style.display = (role === 'etudiant') ? 'block' : 'none';
        document.getElementById('encadrant-fields').style.display = (role === 'encadrant') ? 'block' : 'none';
    }
    </script>
</body>
</html>
