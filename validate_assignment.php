<?php
session_start();

// Check if the logged-in user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: authentification.php'); // Redirect to login page if the user is not an admin
    exit;
}

require 'connexion.php'; // Make sure this path is correct based on your file structure

// Check if the parameters are set
if (!isset($_GET['etudiant_id']) || !isset($_GET['encadrant_id'])) {
    header('Location: admin_dashboard.php'); // Redirect back if parameters are missing
    exit;
}

// Get student and advisor IDs from the query parameters
$etudiant_id = $_GET['etudiant_id'];
$encadrant_id = $_GET['encadrant_id'];

// Validate the assignment by updating the `valide_par_chef` field in the affectations table
$sql = "UPDATE affectations SET valide_par_chef = 1 WHERE etudiant_id = :etudiant_id AND encadrant_id = :encadrant_id";
$stmt = $conn->prepare($sql);

// Execute the query
try {
    $stmt->execute(['etudiant_id' => $etudiant_id, 'encadrant_id' => $encadrant_id]);
    // Redirect to the admin dashboard after validation
    header('Location: dashboards/affectations.php?validated=1');
    exit;
} catch (PDOException $e) {
    // Handle error, maybe log it or display a message
    echo "Error: " . $e->getMessage();
    exit;
}
?>
