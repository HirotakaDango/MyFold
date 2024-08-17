<?php
require 'config.php';
header('Content-Type: application/json');
session_start();

// Check if uid parameter is provided
if (!isset($_GET['uid'])) {
  echo json_encode(['error' => 'User ID not provided.']);
  exit();
}

$user_id = (int)$_GET['uid'];

// Fetch all images for the specified user
$stmt = $db->prepare("SELECT * FROM images WHERE user_id = :user_id");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();

$images = [];
$totalSize = 0; // Initialize total size
$imageCount = 0; // Initialize image count

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  // Adjust the path to include 'original/' before the filename
  $filePath = 'original/' . $row['filename'];

  // Check if file exists and get its size
  if (file_exists($filePath)) {
    $fileSize = filesize($filePath); // Size in bytes
    $totalSize += $fileSize; // Accumulate total size
    $imageCount++; // Increment image count
  }

  $row['path'] = $filePath;
  $images[] = $row;
}

// Function to extract numeric part from filenames
function extractNumericPart($filename) {
  $nameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
  return intval($nameWithoutExtension); // Convert to integer
}

// Sort images by the numeric part of the filename
usort($images, function($a, $b) {
  $numA = extractNumericPart($a['filename']);
  $numB = extractNumericPart($b['filename']);
  return $numB - $numA; // Sort in descending order
});

// Calculate total size in megabytes
$totalSizeMB = $totalSize / (1024 * 1024); // Convert bytes to MB

// Output the images and statistics as pretty-printed JSON
$response = [
  'images' => $images,
  'image_count' => $imageCount,
  'total_size_mb' => number_format($totalSizeMB, 2) // Format to 2 decimal places
];
echo json_encode($response, JSON_PRETTY_PRINT);
?>