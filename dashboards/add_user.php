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
    $prenom  = trim($_POST['prenom'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $role    = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($nom === '' || $prenom === '' || $email === '' || $password === '' || $role === '') {
        $errors[] = "Tous les champs obligatoires doivent être remplis.";
    }

    if (!in_array($role, ['admin', 'encadrant', 'etudiant'])) {
        $errors[] = "Rôle invalide.";
    }

    if (empty($errors)) {
        // Hasher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Base insert
        $sql = "INSERT INTO users (nom, prenom, email, mot_de_passe, role";
        $params = [$nom, $prenom, $email, $hashedPassword, $role];
        
        // Add student-specific fields
        if ($role === 'etudiant') {
            $numero_inscription = trim($_POST['numero_inscription'] ?? '');
            $parcours = trim($_POST['parcours'] ?? '');
            $moyenne_1 = floatval($_POST['moyenne_1ere_annee'] ?? 0);
            $moyenne_2 = floatval($_POST['moyenne_2eme_annee'] ?? 0);
            
            $sql .= ", numero_inscription, parcours, moyenne_1ere_annee, moyenne_2eme_annee";
            $params[] = $numero_inscription;
            $params[] = $parcours;
            $params[] = $moyenne_1;
            $params[] = $moyenne_2;
        }
        
        // Add advisor-specific fields
        if ($role === 'encadrant') {
            $quota_max = intval($_POST['quota_max'] ?? 5);
            $sql .= ", quota_max";
            $params[] = $quota_max;
        }
        
        $sql .= ") VALUES (" . implode(',', array_fill(0, count($params), '?')) . ")";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

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
        <label>Nom :</label><br>
        <input type="text" name="nom" required><br><br>
        
        <label>Prénom :</label><br>
        <input type="text" name="prenom" required><br><br>

        <label>Email :</label><br>
        <input type="email" name="email" required><br><br>

        <label>Mot de passe :</label><br>
        <input type="password" name="password" required><br><br>

        <label>Rôle :</label><br>
        <select name="role" id="role" required onchange="toggleRoleFields()">
            <option value="">-- Choisir un rôle --</option>
            <option value="admin">Admin</option>
            <option value="encadrant">Encadrant</option>
            <option value="etudiant">Étudiant</option>
        </select><br><br>
        
        <div id="etudiant-fields" style="display:none;">
            <label>Numéro d'inscription :</label><br>
            <input type="text" name="numero_inscription"><br><br>
            
            <label>Parcours :</label><br>
            <input type="text" name="parcours"><br><br>
            
            <label>Moyenne 1ère année :</label><br>
            <input type="number" step="0.01" name="moyenne_1ere_annee"><br><br>
            
            <label>Moyenne 2ème année :</label><br>
            <input type="number" step="0.01" name="moyenne_2eme_annee"><br><br>
        </div>
        
        <div id="encadrant-fields" style="display:none;">
            <label>Quota maximum d'étudiants :</label><br>
            <input type="number" name="quota_max" value="5"><br><br>
        </div>

        <button type="submit">Ajouter</button>
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
