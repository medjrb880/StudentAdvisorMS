<?php
session_start();

// Only admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../authentification.php");
    exit;
}

require '../connexion.php';

// Delete user if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $deleteId]);
    header("Location: manage_accounts.php");
    exit;
}

// Fetch all users
$stmt = $conn->query("SELECT id, nom, email, role FROM users ORDER BY role ASC, nom ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les comptes</title>
    <link rel="stylesheet" href="manage.css">
</head>
<body>
<?php if (!empty($_SESSION['delete_error'])): ?>
    <p style="color: red;"><?= htmlspecialchars($_SESSION['delete_error']) ?></p>
    <?php unset($_SESSION['delete_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['delete_success'])): ?>
    <p style="color: green;"><?= htmlspecialchars($_SESSION['delete_success']) ?></p>
    <?php unset($_SESSION['delete_success']); ?>
<?php endif; ?>

    <h1>Gérer les comptes utilisateurs</h1>
    <a href="admin_dashboard.php">← Retour au tableau de bord</a><br><br>
    <a href="add_user.php">➕ Ajouter un nouvel utilisateur</a><br><br>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($users): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['nom']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $user['id'] ?>">✏️ Modifier</a> |
                        <a href="manage_accounts.php?delete=<?= $user['id'] ?>" onclick="return confirm('Confirmer la suppression ?')">🗑️ Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">Aucun utilisateur trouvé.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
