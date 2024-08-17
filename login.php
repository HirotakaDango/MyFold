<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

  if ($result && password_verify($password, $result['password'])) {
    $_SESSION['user_id'] = $result['id'];
    header('Location: index.php');
    exit();
  } else {
    $error_message = "Invalid login credentials.";
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php include('bootstrap.php'); ?>
  </head>
  <body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
      <div>
      <h2 class="fw-bold text-center mb-3">Login</h2>
  
      <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert">
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>
  
      <form method="post">
        <div class="form-group mb-2">
          <label for="email">Email address</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group mb-2">
          <label for="password">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
  
      <div class="mt-3">
        <p>Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
      </div>
      </div>
    </div>
  </body>
</html>