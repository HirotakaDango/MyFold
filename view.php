<?php
require 'config.php';

if (!isset($_GET['id'])) {
  echo "Error: Image ID not provided.";
  exit();
}

$uniqid = $_GET['id'];

// Prepare and execute the SQL query with a join
$stmt = $db->prepare("
  SELECT images.*, users.username 
  FROM images 
  LEFT JOIN users ON images.user_id = users.id 
  WHERE images.uniqid = :uniqid
");
$stmt->bindValue(':uniqid', $uniqid, SQLITE3_TEXT);
$row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$row) {
  echo "Error: Image not found.";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Image</title>
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
              <a class="nav-link" href="setting.php">Setting</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container-fluid mt-4 mb-5">
      <div class="row">
        <div class="col-md-9 order-md-2 mb-5 mb-md-0">
          <?php
          // Function to check if file size is above 2MB
          function isLargeImage($filename) {
            $filepath = 'original/' . $filename;
            $filesize = filesize($filepath); // in bytes
            $filesize_kb = $filesize / 1024; // in KB
            return $filesize_kb > 2048; // 2MB in KB
          }

          $image_filename = $row['filename'];
          $src = isLargeImage($image_filename) ? 'thumbnails/' . $image_filename : 'original/' . $image_filename;
          ?>
          <img src="<?php echo $src; ?>" class="w-100 rounded object-fit-cover shadow">
        </div>
        <div class="col-md-3 order-md-1">
          <div class="metadata">
            <h5 class="fw-bold">Informations</h5>
            <?php
            $image_path = 'original/' . $row['filename'];
            $file_info = getimagesize($image_path);
            $file_size = filesize($image_path);
            $file_size_kb = number_format($file_size / 1024, 2); // Convert to KB
            $image_width = $file_info[0];
            $image_height = $file_info[1];
            $image_type = image_type_to_mime_type($file_info[2]);
            $original_image_size = round(filesize('original/' . $row['filename']) / (1024 * 1024), 2);
            $thumbnail_image_size = round(filesize('thumbnails/' . $row['filename']) / (1024 * 1024), 2);
            $reduction_percentage = ((($original_image_size - $thumbnail_image_size) / $original_image_size) * 100);
            ?>
            <h6 class="fw-medium small">Uploaded by: <?php echo $row['username']; ?></h6>
            <h6 class="fw-medium small">Image ID: <?php echo $_GET['id']; ?></h6>
            <h6 class="fw-medium small">Filename: <?php echo $row['filename']; ?></h6>
            <h6 class="fw-medium small">Compressed: <?php echo round($reduction_percentage, 2); ?>%</h6>
            <h6 class="fw-medium small">Date: <?php echo date("l, d F, Y", filemtime($image_path)); ?></h6>
            <h6 class="fw-medium small">Size: <?php echo $file_size_kb; ?> KB</h6>
            <h6 class="fw-medium small">Dimensions: <?php echo $image_width; ?>x<?php echo $image_height; ?></h6>
            <h6 class="fw-medium small">Type: <?php echo $image_type; ?></h6>
            <h5 class="fw-bold mt-4">Options</h5>
            <a class="text-decoration-none fw-medium small" href="#" data-bs-toggle="modal" data-bs-target="#shareLink">Share</a></br>
            <a class="text-decoration-none fw-medium small" href="original/<?php echo $row['filename']; ?>" download>Download</a></br>
            <a class="text-decoration-none fw-medium small" href="original/<?php echo $row['filename']; ?>">View original</a>
            <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-transparent border-0 rounded-0">
                  <div class="card rounded-4 p-4">
                    <p class="text-start fw-bold">share to:</p>
                    <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                      <!-- Twitter -->
                      <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-twitter"></i>
                      </a>
                      <!-- Line -->
                      <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-line"></i>
                      </a>
                      <!-- Email -->
                      <a class="btn" href="mailto:?body=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
                        <i class="bi bi-envelope-fill"></i>
                      </a>
                      <!-- Reddit -->
                      <a class="btn" href="https://www.reddit.com/submit?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-reddit"></i>
                      </a>
                      <!-- Instagram -->
                      <a class="btn" href="https://www.instagram.com/?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-instagram"></i>
                      </a>
                      <!-- Facebook -->
                      <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-facebook"></i>
                      </a>
                    </div>
                    <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                      <!-- WhatsApp -->
                      <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-whatsapp"></i>
                      </a>
                      <!-- Pinterest -->
                      <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-pinterest"></i>
                      </a>
                      <!-- LinkedIn -->
                      <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-linkedin"></i>
                      </a>
                      <!-- Messenger -->
                      <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-messenger"></i>
                      </a>
                      <!-- Telegram -->
                      <a class="btn" href="https://telegram.me/share/url?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-telegram"></i>
                      </a>
                      <!-- Snapchat -->
                      <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-snapchat"></i>
                      </a>
                    </div>
                    <div class="input-group">
                      <input type="text" id="urlInput1" value="<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" class="form-control border-2 fw-bold" readonly>
                      <button class="btn btn-secondary opacity-50 fw-bold" onclick="copyToClipboard1()">
                        <i class="bi bi-clipboard-fill"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script>
      function copyToClipboard1() {
        var copyText = document.getElementById("urlInput1");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        document.execCommand("copy");
        alert("Copied the text: " + copyText.value);
      }
    </script>
  </body>
</html>
