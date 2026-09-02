<?php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $parol = trim($_POST['parol'] ?? '');
    $job = trim($_POST['job'] ?? 'Lugeja');
    $telefon = trim($_POST['telefon'] ?? '+3725000000');

    if (!empty($name) && !empty($login) && !empty($parol) && !empty($email)) {
        $escapedLogin = $conn->real_escape_string($login);
        $check = $conn->query("SELECT id FROM users WHERE login = '$escapedLogin'");
        if ($check && $check->num_rows > 0) {
            $error = 'See kasutajanimi on juba võetud!';
        } else {
            $eName = $conn->real_escape_string($name);
            $eEmail = $conn->real_escape_string($email);
            $eParol = $conn->real_escape_string($parol);
            $eJob = $conn->real_escape_string($job);
            $eTel = $conn->real_escape_string($telefon);

            $sql = "INSERT INTO users (name, job, email, telefon, login, parol, status, registratsion_date)
                    VALUES ('$eName', '$eJob', '$eEmail', '$eTel', '$escapedLogin', '$eParol', 'user', CURDATE())";
            
            if ($conn->query($sql)) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = 'user';
                header("Location: index.php");
                exit;
            } else {
                $error = 'Andmebaasi viga: ' . $conn->error;
            }
        }
    } else {
        $error = 'Palun täida kõik nõutud väljad!';
    }
}

$pageTitle = 'Registreeru';
include 'includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Loo uus konto</h2>
            <p>Liitu NewsPortal kogukonnaga</p>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.875rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label>Täisnimi</label>
                <input type="text" name="name" required class="form-control" placeholder="Mati Maasikas">
            </div>

            <div class="form-group">
                <label>E-post</label>
                <input type="email" name="email" required class="form-control" placeholder="mati@example.ee">
            </div>

            <div class="form-group">
                <label>Kasutajanimi</label>
                <input type="text" name="login" required class="form-control" placeholder="mati23">
            </div>

            <div class="form-group">
                <label>Parool</label>
                <input type="password" name="parol" required class="form-control" placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Loo konto</button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 0.875rem; color: var(--text-secondary);">
            Konto juba olemas? <a href="login.php" style="color: var(--primary); font-weight: 700;">Logi sisse</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
