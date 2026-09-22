<?php
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Turvakontroll ebaõnnestus (vigane CSRF luba). Palun proovige uuesti.';
    } else {
        $login = trim($_POST['login'] ?? '');
        $parol = trim($_POST['parol'] ?? '');

        if (!empty($login) && !empty($parol)) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE login = ? OR email = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("ss", $login, $login);
                $stmt->execute();
                $res = $stmt->get_result();

                if ($res && $res->num_rows > 0) {
                    $user = $res->fetch_assoc();
                    $isPasswordCorrect = false;

                    if (password_verify($parol, $user['parol'])) {
                        $isPasswordCorrect = true;
                        // Auto rehash if algorithm cost changed
                        if (password_needs_rehash($user['parol'], PASSWORD_DEFAULT)) {
                            $newHash = password_hash($parol, PASSWORD_DEFAULT);
                            $rehashStmt = $conn->prepare("UPDATE users SET parol = ? WHERE id = ?");
                            if ($rehashStmt) {
                                $rehashStmt->bind_param("si", $newHash, $user['id']);
                                $rehashStmt->execute();
                            }
                        }
                    } elseif ($user['parol'] === $parol) {
                        // Transparently migrate plain-text legacy password to bcrypt hash
                        $isPasswordCorrect = true;
                        $newHash = password_hash($parol, PASSWORD_DEFAULT);
                        $rehashStmt = $conn->prepare("UPDATE users SET parol = ? WHERE id = ?");
                        if ($rehashStmt) {
                            $rehashStmt->bind_param("si", $newHash, $user['id']);
                            $rehashStmt->execute();
                        }
                    }

                    if ($isPasswordCorrect) {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_role'] = $user['status'];
                        
                        if (is_admin($user) || is_editor_or_admin($user)) {
                            header("Location: admin/index.php");
                        } else {
                            header("Location: index.php");
                        }
                        exit;
                    } else {
                        $error = 'Vale parool!';
                    }
                } else {
                    $error = 'Kasutajat ei leitud!';
                }
            } else {
                $error = 'Päringu ettevalmistamise viga: ' . $conn->error;
            }
        } else {
            $error = 'Palun täida kõik väljad!';
        }
    }
}

$pageTitle = 'Logi sisse';
include 'includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Tere tulemast tagasi</h2>
            <p>Logi sisse oma NewsPortal kontole</p>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.875rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?= csrf_input() ?>
            <div class="form-group">
                <label>Kasutajanimi või E-post</label>
                <input type="text" name="login" id="loginField" required class="form-control" placeholder="admin või user" value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '' ?>">
            </div>

            <div class="form-group">
                <label>Parool</label>
                <input type="password" name="parol" id="passwordField" required class="form-control" placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Logi sisse</button>
        </form>

        <div class="demo-credentials-box">
            <strong>Kiirtesti demoga:</strong>
            <div class="demo-btn-row">
                <button type="button" class="btn-demo" onclick="fillCreds('admin', 'admin123')">🔑 Admin (Peatoimetaja)</button>
                <button type="button" class="btn-demo" onclick="fillCreds('user', 'user123')">👤 Tavaline lugeja</button>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px; font-size: 0.875rem; color: var(--text-secondary);">
            Pole veel kontot? <a href="register.php" style="color: var(--primary); font-weight: 700;">Registreeru siin</a>
        </div>
    </div>
</div>

<script>
function fillCreds(u, p) {
    document.getElementById('loginField').value = u;
    document.getElementById('passwordField').value = p;
}
</script>

<?php include 'includes/footer.php'; ?>
