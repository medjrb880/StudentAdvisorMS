<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../authentification.php');
    exit;
}

require '../connexion.php';

// Step 1: Fetch students with their average score
$studentsStmt = $conn->query("
    SELECT 
        u.id AS etudiant_id, 
        u.moyenne_1ere_annee, 
        u.moyenne_2eme_annee,
        p.choix1_id, 
        p.choix2_id, 
        p.choix3_id
    FROM users u
    JOIN preferences p ON u.id = p.etudiant_id
    LEFT JOIN affectations a ON p.etudiant_id = a.etudiant_id
    WHERE u.role = 'etudiant' AND a.id IS NULL
");

// Step 2: Calculate the average score and sort students by descending order
$students = [];
while ($student = $studentsStmt->fetch(PDO::FETCH_ASSOC)) {
    $average_score = ($student['moyenne_1ere_annee'] + 2 * $student['moyenne_2eme_annee']) / 3;
    $student['average_score'] = $average_score;
    $students[] = $student;
}

// Sort students by average_score in descending order
usort($students, function($a, $b) {
    return $b['average_score'] <=> $a['average_score'];
});

// Step 3: Fetch encadrant quotas and current counts
$encadrantsStmt = $conn->query("
    SELECT u.id, u.quota_max, COUNT(a.id) AS assigned
    FROM users u
    LEFT JOIN affectations a ON u.id = a.encadrant_id
    WHERE u.role = 'encadrant'
    GROUP BY u.id
");

$encadrants = [];
foreach ($encadrantsStmt as $row) {
    $encadrants[$row['id']] = [
        'quota_max' => $row['quota_max'],
        'assigned' => $row['assigned']
    ];
}

// Step 4: Begin assignment logic (based on sorted students)
foreach ($students as $student) {
    $etudiant_id = $student['etudiant_id'];
    $preferences = [$student['choix1_id'], $student['choix2_id'], $student['choix3_id']];
    $assigned = false;

    // Try to assign based on the sorted choices
    foreach ($preferences as $encadrant_id) {
        if ($encadrant_id && isset($encadrants[$encadrant_id])) {
            if ($encadrants[$encadrant_id]['assigned'] < $encadrants[$encadrant_id]['quota_max']) {
                // Assign student
                $stmt = $conn->prepare("
                    INSERT INTO affectations (etudiant_id, encadrant_id, date_affectation, valide_par_chef)
                    VALUES (:etudiant_id, :encadrant_id, NOW(), 0)
                ");
                $stmt->execute([
                    'etudiant_id' => $etudiant_id,
                    'encadrant_id' => $encadrant_id
                ]);

                $encadrants[$encadrant_id]['assigned']++;
                $assigned = true;
                break;
            }
        }
    }

    // Optionally: log or report if no encadrant could be assigned
    if (!$assigned) {
        error_log("Aucun encadrant disponible pour l'étudiant ID $etudiant_id");
    }
}

// Redirect after assignment
header('Location: affectations.php?message=assignation_auto_effectuee');
exit;

?>
