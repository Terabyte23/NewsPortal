<?php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Turvakontroll ebaõnnestus (vigane CSRF luba). Palun proovige uuesti.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $login = trim($_POST['login'] ?? '');
        $parol = trim($_POST['parol'] ?? '');
        $job = trim($_POST['job'] ?? 'Lugeja');
        $telefon = trim($_POST['telefon'] ?? '+3725000000');

        if (!empty($name) && !empty($login) && !empty($parol) && !empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Palun sisestage kehtiv e-posti aadress!';
            } elseif (mb_strlen($parol) < 4) {
                $error = 'Parool peab olema vähemalt 4 tähemärki pikk!';
            } else {
                // Check if username already exists
                $stmtCheck = $conn->prepare("SELECT id FROM users WHERE login = ? LIMIT 1");
                $stmtCheck->bind_param("s", $login);
                $stmtCheck->execute();
                $check = $stmtCheck->get_result();

                if ($check && $check->num_rows > 0) {
                    $error = 'See kasutajanimi on juba võetud!';
                } else {
                    $hashedPassword = password_hash($parol, PASSWORD_DEFAULT);

                    $stmtInsert = $conn->prepare("INSERT INTO users (name, job, email, telefon, login, parol, status, registratsion_date)
                                                  VALUES (?, ?, ?, ?, ?, ?, 'user', CURDATE())");
                    if ($stmtInsert) {
                        $stmtInsert->bind_param("ssssss", $name, $job, $email, $telefon, $login, $hashedPassword);
                        if ($stmtInsert->execute()) {
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $conn->insert_id;
                            $_SESSION['user_name'] = $name;
                            $_SESSION['user_role'] = 'user';
                            header("Location: index.php");
                            exit;
                        } else {
                            $error = 'Andmebaasi viga: ' . $stmtInsert->error;
                        }
                    } else {
                        $error = 'Päringu ettevalmistamise viga: ' . $conn->error;
                    }
                }
            }
        } else {
            $error = 'Palun täida kõik nõutud väljad!';
        }
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
            <?= csrf_input() ?>
            <div class="form-group">
                <label>Täisnimi</label>
                <input type="text" name="name" required class="form-control" placeholder="Mati Maasikas" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
            </div>

            <div class="form-group">
                <label>E-post</label>
                <input type="email" name="email" required class="form-control" placeholder="mati@example.ee" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>

            <div class="form-group">
                <label>Kasutajanimi</label>
                <input type="text" name="login" required class="form-control" placeholder="mati23" value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '' ?>">
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
