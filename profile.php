<?php
require_once 'db.php';

$currentUser = get_logged_in_user($conn);
if (!$currentUser) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefon = trim($_POST['telefon'] ?? '');
    $job = trim($_POST['job'] ?? '');
    $newParol = trim($_POST['new_parol'] ?? '');

    $userId = (int)$currentUser['id'];
    $eName = $conn->real_escape_string($name);
    $eEmail = $conn->real_escape_string($email);
    $eTel = $conn->real_escape_string($telefon);
    $eJob = $conn->real_escape_string($job);

    $sql = "UPDATE users SET name = '$eName', email = '$eEmail', telefon = '$eTel', job = '$eJob'";
    if (!empty($newParol)) {
        $eParol = $conn->real_escape_string($newParol);
        $sql .= ", parol = '$eParol'";
    }
    $sql .= " WHERE id = $userId";

    if ($conn->query($sql)) {
        $success = 'Profiili andmed edukalt uuendatud!';
        $currentUser = get_logged_in_user($conn);
    } else {
        $error = 'Viga andmete salvestamisel: ' . $conn->error;
    }
}

$pageTitle = 'Minu profiil';
include 'includes/header.php';
?>

<main class="container" style="padding: 40px 0 60px 0; max-width: 680px;">
    <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 35px;">
        <div style="display: flex; align-items: center; gap: 18px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
            <div class="avatar-circle" style="width: 60px; height: 60px; font-size: 1.5rem;">
                <?= mb_strtoupper(mb_substr($currentUser['name'] ?? $currentUser['login'], 0, 1)) ?>
            </div>
            <div>
                <h2 style="font-size: 1.5rem; font-weight: 900;"><?= htmlspecialchars($currentUser['name']) ?></h2>
                <span class="user-role-badge role-<?= strtolower($currentUser['status']) ?>"><?= htmlspecialchars($currentUser['status']) ?></span>
                <span style="font-size: 0.75rem; color: var(--text-muted); margin-left: 8px;">Liitunud: <?= $currentUser['registratsion_date'] ?></span>
            </div>
        </div>

        <?php if ($success): ?>
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.875rem;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
            <div class="form-group">
                <label>Täisnimi</label>
                <input type="text" name="name" value="<?= htmlspecialchars($currentUser['name']) ?>" required class="form-control">
            </div>

            <div class="form-group">
                <label>E-post</label>
                <input type="email" name="email" value="<?= htmlspecialchars($currentUser['email']) ?>" required class="form-control">
            </div>

            <div class="form-group">
                <label>Telefon</label>
                <input type="text" name="telefon" value="<?= htmlspecialchars($currentUser['telefon']) ?>" class="form-control">
            </div>

            <div class="form-group">
                <label>Amet / Roll</label>
                <input type="text" name="job" value="<?= htmlspecialchars($currentUser['job']) ?>" class="form-control">
            </div>

            <div class="form-group">
                <label>Uus parool (jäta tühjaks, kui ei soovi muuta)</label>
                <input type="password" name="new_parol" placeholder="••••••••" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Salvesta muudatused</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
