<?php
include 'templates/header.php';
include 'includes/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$usernameOrEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username_or_email'] ?? '');
    $password        = $_POST['password'] ?? '';

    if ($usernameOrEmail === '' || $password === '') {
        $errors[] = 'Please fill in all fields.';
    } else {
        $stmt = $mysqli->prepare("SELECT user_id, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param('ss', $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            // Store only what you need
            $_SESSION['user'] = [
                'id'       => $user['user_id'],
                'username' => $user['username'],
                'role'     => $user['role'] ?? 'user'
            ];

            // Redirect based on role
             header('Location: index.php');
    exit;
        } else {
            $errors[] = 'Invalid credentials.';
        }
    }
}
?>

<div class="row justify-content-center">
  <div class="col-md-5 col-lg-4">
    <h2 class="mb-4 text-center">Login</h2>

    <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
      <div class="alert alert-success">Account created! Please log in.</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label" for="username_or_email">Username or Email</label>
        <input type="text" class="form-control" id="username_or_email" name="username_or_email"
               value="<?php echo htmlspecialchars($usernameOrEmail); ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Login</button>

      <p class="text-center mt-3">
        Don’t have an account? <a href="register.php">Register</a>
      </p>
    </form>
  </div>
</div>

<?php include 'templates/footer.php'; ?>
