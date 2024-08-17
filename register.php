<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

  $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
  $stmt->bindValue(':username', $username, SQLITE3_TEXT);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':password', $password, SQLITE3_TEXT);

  if ($stmt->execute()) {
    // Registration successful, redirect to index.php
    header('Location: index.php');
    exit(); // Ensure to exit after redirection
  } else {
    $error_message = "Error: Could not register.";
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <?php include('bootstrap.php'); ?>
  </head>
  <body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
      <div>
        <h2 class="fw-bold text-center mb-3">Register</h2>
  
        <?php if (isset($error_message)): ?>
          <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
          </div>
        <?php endif; ?>
  
        <form method="post">
          <div class="form-group mb-2">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
          </div>
          <div class="form-group mb-2">
            <label for="email">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="form-group mb-2">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
  
        <div class="mt-3">
          <p>Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
        </div>
      </div>
    </div>
  </body>
</html>