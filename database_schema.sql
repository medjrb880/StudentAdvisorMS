-- Database Schema for Gestion Encadrement
-- Drop existing tables if they exist (be careful in production!)
DROP TABLE IF EXISTS affectations;
DROP TABLE IF EXISTS preferences;
DROP TABLE IF EXISTS users;

-- Main users table (for all user types: admin, etudiant, encadrant)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'etudiant', 'encadrant') NOT NULL,
    
    -- Student-specific fields (NULL for non-students)
    numero_inscription VARCHAR(50) NULL,
    parcours VARCHAR(100) NULL,
    moyenne_1ere_annee DECIMAL(4,2) NULL,
    moyenne_2eme_annee DECIMAL(4,2) NULL,
    
    -- Advisor-specific fields (NULL for non-advisors)
    quota_max INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Student preferences table
CREATE TABLE preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    choix1_id INT NULL,
    choix2_id INT NULL,
    choix3_id INT NULL,
    date_soumission TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (choix1_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (choix2_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (choix3_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_etudiant (etudiant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assignments table
CREATE TABLE affectations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    encadrant_id INT NOT NULL,
    date_affectation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valide_par_chef TINYINT(1) DEFAULT 0,
    FOREIGN KEY (etudiant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (encadrant_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_etudiant_assignment (etudiant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin account (password: admin123)
INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES
('Admin', 'System', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample advisors (password: password123 for all)
INSERT INTO users (nom, prenom, email, mot_de_passe, role, quota_max) VALUES
('Dupont', 'Jean', 'jean.dupont@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'encadrant', 5),
('Martin', 'Marie', 'marie.martin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'encadrant', 5),
('Bernard', 'Pierre', 'pierre.bernard@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'encadrant', 5);

-- Sample students (password: password123 for all)
INSERT INTO users (nom, prenom, email, mot_de_passe, role, numero_inscription, parcours, moyenne_1ere_annee, moyenne_2eme_annee) VALUES
('Amari', 'Ahmed', 'ahmed.amari@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant', 'INS001', 'Informatique', 14.5, 15.2),
('Benhamed', 'Fatima', 'fatima.benhamed@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant', 'INS002', 'Informatique', 13.8, 14.5),
('Cherif', 'Mohamed', 'mohamed.cherif@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant', 'INS003', 'Informatique', 15.2, 16.0);
