<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

$user_id = $_SESSION['user_id'];

// Handle delete request
if (isset($_GET['delete_id'])) {
  $delete_id = $_GET['delete_id'];

  // Delete the image record from the database
  $delete_stmt = $db->prepare("DELETE FROM images WHERE user_id = :user_id AND uniqid = :uniqid");
  $delete_stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
  $delete_stmt->bindValue(':uniqid', $delete_id, SQLITE3_TEXT);

  if ($delete_stmt->execute()) {
    // Delete the image file from the server
    $image_file = '/original/' . $delete_id; // Adjust path as needed
    if (file_exists($image_file)) {
      unlink($image_file);
    }
    
    // Redirect to avoid resubmission of the form
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
  } else {
    echo "Error: Could not delete the image.";
  }
}

// Pagination settings
$images_per_page = 24;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $images_per_page;

// Count total images for the user
$count_stmt = $db->prepare("SELECT COUNT(*) AS total FROM images WHERE user_id = :user_id");
$count_stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$count_result = $count_stmt->execute();
$total_images = $count_result->fetchArray(SQLITE3_ASSOC)['total'];
$total_pages = ceil($total_images / $images_per_page);

// Fetch images for the current page
$stmt = $db->prepare("SELECT * FROM images WHERE user_id = :user_id ORDER BY filename DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':limit', $images_per_page, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Images</title>
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
              <a class="nav-link active" href="index.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="upload.php">Upload</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="setting.php">Setting</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container my-5">
      <div class="row row-cols-2 row-cols-md-6 g-2">
        <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
          <div class="col">
            <div class="position-relative">
              <a class="ratio ratio-1x1" href="view.php?id=<?php echo $row['uniqid']; ?>">
                <img src="/thumbnails/<?php echo $row['filename']; ?>" class="w-100 rounded object-fit-cover" alt="<?php echo htmlspecialchars($row['filename']); ?>">
              </a>
              <div class="dropdown position-absolute top-0 end-0">
                <button class="btn border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical text-white fs-5" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);"></i>
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="/original/<?php echo $row['filename']; ?>" download>download</a></li>
                  <li><a class="dropdown-item" href="?delete_id=<?php echo $row['uniqid']; ?>" onclick="return confirm('Are you sure you want to delete this image?');">delete</a></li>
                </ul>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
      <!-- Pagination Controls -->
      <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center mt-4">
          <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
            <a class="page-link" href="?page=<?php echo max($page - 1, 1); ?>" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
              <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
            <a class="page-link" href="?page=<?php echo min($page + 1, $total_pages); ?>" aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </body>
</html>