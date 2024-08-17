<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

function createThumbnail($filepath, $thumbpath, $thumbWidth) {
  list($width, $height, $type) = getimagesize($filepath);
  $src_img = imagecreatefromstring(file_get_contents($filepath));

  $thumbHeight = floor($height * ($thumbWidth / $width));
  $thumb_img = imagecreatetruecolor($thumbWidth, $thumbHeight);

  imagecopyresized($thumb_img, $src_img, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
  imagejpeg($thumb_img, $thumbpath);
  imagedestroy($src_img);
  imagedestroy($thumb_img);
}

$uploadStatus = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['images'])) {
  $uploadedFiles = $_FILES['images'];

  for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
    $filename = basename($uploadedFiles['name'][$i]);
    $uniqid = uniqid();
    $target_file = __DIR__ . "/original/" . $filename;
    $thumbnail_file = __DIR__ . "/thumbnails/" . $filename;

    if (move_uploaded_file($uploadedFiles['tmp_name'][$i], $target_file)) {
      createThumbnail($target_file, $thumbnail_file, 300);

      $stmt = $db->prepare("INSERT INTO images (filename, uniqid, user_id) VALUES (:filename, :uniqid, :user_id)");
      $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
      $stmt->bindValue(':uniqid', $uniqid, SQLITE3_TEXT);
      $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);

      if ($stmt->execute()) {
        $uploadStatus[] = "Image '$filename' uploaded successfully!";
      } else {
        $uploadStatus[] = "Error: Could not save information for image '$filename'.";
      }
    } else {
      $uploadStatus[] = "Error: Could not upload the image '$filename'.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Images</title>
    <?php include('bootstrap.php'); ?>
    <style>
      #progress-container {
        display: none;
        margin-top: 20px;
      }
      #progress-bar {
        width: 0;
        height: 30px;
        background-color: #0d6efd;
        color: #fff;
        text-align: center;
        line-height: 30px;
        border-radius: 5px;
      }
    </style>
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
              <a class="nav-link active" href="upload.php">Upload</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="setting.php">Setting</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container my-5">
      <h1 class="text-center">Upload Your Images</h1>
      <?php if (!empty($uploadStatus)): ?>
        <div class="alert alert-info">
          <?php foreach ($uploadStatus as $status): ?>
            <p><?php echo htmlspecialchars($status); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <form id="upload-form" method="post" enctype="multipart/form-data" class="mt-4">
        <div class="mb-3">
          <label for="images" class="form-label">Select Images to Upload</label>
          <input type="file" name="images[]" id="images" class="form-control" multiple required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Upload Images</button>
      </form>
      <div id="progress-container">
        <div id="progress-bar">0%</div>
      </div>
    </div>
    <script>
      document.getElementById('upload-form').addEventListener('submit', function(event) {
        event.preventDefault();
    
        var formData = new FormData(this);
        var files = formData.getAll('images[]');
        var totalFiles = files.length;
        var currentFileIndex = 0;
    
        function uploadFile(file) {
          var xhr = new XMLHttpRequest();
          var fileFormData = new FormData();
          fileFormData.append('images[]', file);
    
          xhr.open('POST', '', true);
    
          xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
              var percentComplete = Math.round((e.loaded / e.total) * 100);
              document.getElementById('progress-container').style.display = 'block';
              document.getElementById('progress-bar').style.width = percentComplete + '%';
              document.getElementById('progress-bar').textContent = percentComplete + '%';
            }
          });
    
          xhr.addEventListener('load', function() {
            if (xhr.status === 200) {
              currentFileIndex++;
              if (currentFileIndex < totalFiles) {
                uploadFile(files[currentFileIndex]);
              } else {
                document.getElementById('progress-bar').textContent = 'All uploads complete!';
              }
            } else {
              document.getElementById('progress-bar').textContent = 'Upload failed for ' + file.name;
              currentFileIndex++;
              if (currentFileIndex < totalFiles) {
                uploadFile(files[currentFileIndex]);
              }
            }
          });
    
          xhr.send(fileFormData);
        }
    
        if (totalFiles > 0) {
          uploadFile(files[currentFileIndex]);
        }
      });
    </script>
  </body>
</html>