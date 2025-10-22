<?php
session_start();
require '../connexion.php'; // Ajuste le chemin si besoin

// 1) Vérifier le rôle
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'etudiant') {
    header('Location: ../authentification.php');
    exit;
}

// 2) Récupérer l'ID étudiant depuis la session
$etudiant_id = $_SESSION['user_id'] ?? null;
if (!$etudiant_id) {
    die("Utilisateur non connecté.");
}

// 3) Vérifier que l'étudiant existe dans la table `etudiants`
$check_student = $conn->prepare("SELECT id FROM etudiants WHERE id = ?");
$check_student->execute([$etudiant_id]);
if (!$check_student->fetch()) {
    die("L'étudiant n'existe pas dans la base de données.");
}

// 4) Empêcher un second envoi
$check_pref = $conn->prepare("SELECT id FROM preferences WHERE etudiant_id = ?");
$check_pref->execute([$etudiant_id]);
if ($check_pref->fetch()) {
    header('Location: mon_encadrant.php');
    exit;
}

// 5) Si c'est un POST, traiter le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $choix1 = $_POST['choix1'] ?? '';
    $choix2 = $_POST['choix2'] ?? '';
    $choix3 = $_POST['choix3'] ?? '';

    // 5a) Validation : soit aucun choix, soit exactement 3 différents
    $allEmpty = ($choix1 === '' && $choix2 === '' && $choix3 === '');
    $allFilled = ($choix1 !== '' && $choix2 !== '' && $choix3 !== '');
    if (!($allEmpty || $allFilled)) {
        echo "<script>alert('Vous devez soit sélectionner 3 encadrants, soit ne sélectionner aucun.'); window.location.href='etudiant_dashboard.php';</script>";
        exit;
    }
    if ($allFilled && ($choix1 === $choix2 || $choix1 === $choix3 || $choix2 === $choix3)) {
        echo "<script>alert('Vous ne pouvez pas sélectionner deux fois le même encadrant.'); window.location.href='etudiant_dashboard.php';</script>";
        exit;
    }

    // 5b) Si 3 choix, vérifier qu'ils existent bien et sont des encadrants
    if ($allFilled) {
        $encs = [$choix1, $choix2, $choix3];
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE id = ? AND role = 'encadrant'");
        foreach ($encs as $idEnc) {
            $stmt->execute([$idEnc]);
            if ($stmt->fetchColumn() == 0) {
                echo "<script>alert('Encadrant invalide sélectionné.'); window.location.href='etudiant_dashboard.php';</script>";
                exit;
            }
        }
    }

    // 6) Insérer (même si allEmpty = true, on peut décider d'enregistrer un record vide ou rediriger)
    if ($allFilled) {
        $insert = $conn->prepare(
            "INSERT INTO preferences 
             (etudiant_id, choix1_id, choix2_id, choix3_id, date_soumission) 
             VALUES (?, ?, ?, ?, NOW())"
        );
        $insert->execute([$etudiant_id, $choix1, $choix2, $choix3]);
    } else {
        // Optionnel : si tu veux enregis. un record vide
        $insert = $conn->prepare(
            "INSERT INTO preferences (etudiant_id, date_soumission) VALUES (?, NOW())"
        );
        $insert->execute([$etudiant_id]);
    }

    // 7) Rediriger vers la page d’affectation
    header('Location: mon_encadrant.php');
    exit;
}
