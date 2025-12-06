<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:red; text-align:center; font-weight:bold;'>⚠️ You must be logged in to view this page.</p>";
    exit;
}

// Load from session
$username = htmlspecialchars($_SESSION['username']);
$prenom   = htmlspecialchars($_SESSION['prenom'] ?? 'User');
$email    = htmlspecialchars($_SESSION['email']);
$phone    = htmlspecialchars($_SESSION['phone'] ?? 'N/A');
?>
<body>
  <div class="account-page">
    <h2>Login &amp; Security</h2>

    <div class="account-section">
      <label>Name</label><span><?= "$prenom $username" ?></span><button>Edit</button>
    </div>

    <div class="account-section">
      <label>Email</label><span><?= $email ?></span><button>Edit</button>
    </div>

    <div class="account-section">
      <label>Mobile Number</label>
      <span><?= $phone ?><br><small>This number is used for account recovery.</small></span>
      <button>Edit</button>
    </div>

    <div class="account-section">
      <label>Password</label><span>********</span><button>Edit</button>
    </div>

    <div class="logout">
      <form action="logout.php" method="post">
        <button class="logout-btn">🚪 Log Out</button>
      </form>
    </div>
  </div>
</body>
<link rel="stylesheet" href="profile.css">
<?php require 'footer.php'; ?>