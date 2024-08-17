<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $new_username = $_POST['username'];
  $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
  $user_id = $_SESSION['user_id'];

  $stmt = $db->prepare("UPDATE users SET username = :username, password = :password WHERE id = :id");
  $stmt->bindValue(':username', $new_username, SQLITE3_TEXT);
  $stmt->bindValue(':password', $new_password, SQLITE3_TEXT);
  $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);

  if ($stmt->execute()) {
    $message = "Settings updated successfully!";
  } else {
    $message = "Error: Could not update settings.";
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Settings</title>
    <?php include('bootstrap.php'); ?>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <a class="navbar-brand" href="/">MyFold</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="upload.php">Upload</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="setting.php">Setting</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container my-5">
      <h1 class="text-center mb-4">Update Your Settings</h1>
    
      <?php if (isset($message)): ?>
        <div class="alert alert-info text-center">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>
    
      <form method="post" class="bg-body-tertiary p-4 rounded-4 shadow-sm">
        <div class="mb-3">
          <label for="username" class="form-label">New Username</label>
          <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">New Password</label>
          <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Settings</button>
      </form>
    </div>
  </body>
</html>