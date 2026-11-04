<?php
require_once __DIR__ . '/../includes/functions.php';

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (csrf_ok() && hash_equals(ADMIN_PASSWORD, (string)$_POST['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    }
    $loginError = 'Wrong password.';
}
$isAdmin = !empty($_SESSION['admin']);

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'], $_POST['id'])) {
    if (csrf_ok() && in_array($_POST['status'], ['pending', 'confirmed', 'cancelled'], true)) {
        $st = db()->prepare('UPDATE reservations SET status = ? WHERE id = ?');
        $st->execute([$_POST['status'], (int)$_POST['id']]);
    }
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin | <?= e(SITE_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Rozha+One&family=Hind:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css?v=<?= (int)@filemtime(__DIR__ . '/../assets/style.css') ?>">
</head>
<body>
<div class="wrap admin">
<?php if (!$isAdmin): ?>
  <h1>Admin sign in</h1>
  <?php if ($loginError): ?><p class="msg err"><?= e($loginError) ?></p><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label>Password<input type="password" name="password" required autofocus></label>
    <button class="btn" type="submit">Sign in</button> <a class="btn" href="../index.php">View site</a>
  </form>
<?php else:
  $rows = db()->query('SELECT * FROM reservations ORDER BY reserve_date, reserve_time')->fetchAll(); ?>
  <p><a class="btn" href="../index.php">View site</a> | <a class="btn" href="?logout=1">Sign out</a></p>
  <h1>Reservations</h1>
  <div class="tw"><table>
    <thead><tr><th>Ref</th><th>Date</th><th>Time</th><th>Guests</th><th>Guest</th><th>Contact</th><th>Notes</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><strong><?= e($r['ref']) ?></strong></td>
        <td><?= e($r['reserve_date']) ?></td>
        <td><?= e(substr($r['reserve_time'], 0, 5)) ?></td>
        <td><?= (int)$r['guests'] ?></td>
        <td><?= e($r['name']) ?></td>
        <td><?= e($r['email']) ?><br><?= e($r['phone']) ?></td>
        <td><?= e($r['notes']) ?></td>
        <td>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <select name="status" onchange="this.form.submit()">
              <?php foreach (['pending', 'confirmed', 'cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="8">No reservations yet.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
<?php endif; ?>
</div>
</body>
</html>
