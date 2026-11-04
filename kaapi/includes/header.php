<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e(SITE_NAME) ?> | Filter coffee house, Bengaluru</title>
<link href="https://fonts.googleapis.com/css2?family=Rozha+One&family=Hind:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=<?= (int)@filemtime(__DIR__ . '/../assets/style.css') ?>">
</head>
<body>
<header>
  <a class="logo" href="index.php"><?= e(SITE_NAME) ?></a>
  <nav>
    <a href="index.php#how">The pour</a>
    <a href="index.php#menu">Menu</a>
    <a href="index.php#book">Book a table</a>
    <a href="check.php">Check booking</a>
    <a href="#visit">Visit</a>
  </nav>
</header>
