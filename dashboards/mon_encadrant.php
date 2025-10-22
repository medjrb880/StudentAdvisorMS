<?php
session_start();

// 1) Only students can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header('Location: ../authentification.php');
    exit;
}

require '../connexion.php';  // Connexion à la BDD

$etudiant_id = $_SESSION['user_id'];

// 2) Récupérer l'encadrant validé (valide_par_chef = 1)
$sql = "
  SELECT 
    enc.prenom,
    enc.nom,
    enc.login AS email
  FROM affectations a
  JOIN encadrants enc 
    ON a.encadrant_id = enc.id
  WHERE a.etudiant_id = :etudiant_id
    AND a.valide_par_chef = 1
  LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->execute(['etudiant_id' => $etudiant_id]);
$encadrant = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon Encadrant</title>
  <link rel="stylesheet" href="mon.css">
</head>
<body>
  <div class="container">
    <h1>Encadrant Assigné</h1>

    <?php if ($encadrant): ?>
      <div class="card">
        <h2>
          <?= htmlspecialchars($encadrant['prenom'] . ' ' . $encadrant['nom']) ?>
        </h2>
        <p>Email : <?= htmlspecialchars($encadrant['email']) ?></p>
      </div>
    <?php else: ?>
      <p>Aucun encadrant ne vous a encore été assigné ou la validation est en attente.</p>
    <?php endif; ?>

    <form method="post" action="reset_etudiant.php" onsubmit="return confirm('Êtes-vous sûr de vouloir réinitialiser ?');">
    <button type="submit" style="margin-top: 20px;">Réinitialiser les affectations</button>
</form>

    <a href="../logout.php">Déconnexion</a>
  </div>
</body>
</html>
