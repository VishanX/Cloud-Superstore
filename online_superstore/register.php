<?php
include 'templates/header.php';
include 'includes/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ---- CSRF ----
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please refresh and try again.';
    }

    // ---- Sanitize inputs ----
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ---- Validate ----
    if (strlen($username) < 4) {
        $errors[] = 'Username must be at least 4 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // ---- Check for duplicates ----
    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT username, email FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // We need to know which one clashes:
            $stmt->bind_result($existingUsername, $existingEmail); // Not actually fetched; we’ll just run two separate checks
            // Quick re-check for clarity:
            $stmt2 = $mysqli->prepare("SELECT 1 FROM users WHERE username = ? LIMIT 1");
            $stmt2->bind_param('s', $username);
            $stmt2->execute();
            $stmt2->store_result();
            if ($stmt2->num_rows > 0) {
                $errors[] = 'Username is already taken.';
            }
            $stmt2->close();

            $stmt3 = $mysqli->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
            $stmt3->bind_param('s', $email);
            $stmt3->execute();
            $stmt3->store_result();
            if ($stmt3->num_rows > 0) {
                $errors[] = 'Email is already registered.';
            }
            $stmt3->close();
        }
        $stmt->close();
    }

    // ---- Insert if everything is OK ----
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $username, $hash, $email);

        if ($stmt->execute()) {
            // Clean CSRF token so refresh doesn’t reproduce
            unset($_SESSION['csrf_token']);
            header("Location: login.php?registered=1");
            exit;
        } else {
            $errors[] = 'Something went wrong when creating your account. Please try again.';
        }
        $stmt->close();
    }
}
?>

<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <h2 class="mb-4 text-center">Create an Account</h2>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" novalidate id="registerForm">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

      <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <input
          type="text"
          class="form-control"
          id="username"
          name="username"
          value="<?php echo htmlspecialchars($username); ?>"
          required
          minlength="4"
        >
        <div class="form-text">At least 4 characters.</div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input
          type="email"
          class="form-control"
          id="email"
          name="email"
          value="<?php echo htmlspecialchars($email); ?>"
          required
        >
      </div>

      <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
          <input
            type="password"
            class="form-control"
            id="password"
            name="password"
            minlength="6"
            required
          >
          <button class="btn btn-outline-secondary" type="button" id="togglePass">Show</button>
        </div>
        <div class="form-text">At least 6 characters.</div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="confirm_password">Confirm Password</label>
        <input
          type="password"
          class="form-control"
          id="confirm_password"
          name="confirm_password"
          required
        >
      </div>

      <button type="submit" class="btn btn-primary w-100">Register</button>
      <p class="text-center mt-3">
        Already have an account? <a href="login.php">Login</a>
      </p>
    </form>
  </div>
</div>

<script>
// Tiny helper to show/hide password
document.getElementById('togglePass').addEventListener('click', function () {
  const pwd = document.getElementById('password');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    this.textContent = 'Hide';
  } else {
    pwd.type = 'password';
    this.textContent = 'Show';
  }
});
</script>

<?php include 'templates/footer.php'; ?>
