<?php
// config.php
// Database connection
$db = new SQLite3('database.sqlite');

// Create users table
$db->exec("CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  password TEXT NOT NULL
)");

// Create images table
$db->exec("CREATE TABLE IF NOT EXISTS images (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  filename TEXT NOT NULL,
  uniqid TEXT NOT NULL UNIQUE,
  user_id INTEGER NOT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id)
)");

// Create 'original' and 'thumbnails' directories if they do not exist
$directories = ['original', 'thumbnails'];

foreach ($directories as $directory) {
  if (!is_dir($directory)) {
    mkdir($directory, 0755, true);
  }
}
?>