<?php
require_once __DIR__ . '/auth_check.php';
$adminPage = 'users';
$pageTitle = 'Kasutajate haldus';

if (!is_admin($currentUser)) {
    die("Ainult peatoimetaja saab kasutajaid hallata.");
}

$msg = '';
if (isset($_GET['toggle_role']) && isset($_GET['id'])) {
    admin_verify_csrf();
    $uid = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $uRes = $stmt->get_result();
        if ($uRes && $uRes->num_rows > 0) {
            $curStatus = $uRes->fetch_assoc()['status'];
            $newStatus = ($curStatus === 'admin') ? 'user' : 'admin';
            $upStmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            if ($upStmt) {
                $upStmt->bind_param("si", $newStatus, $uid);
                $upStmt->execute();
                $msg = 'Kasutaja roll muudetud!';
            }
        }
    }
}

if (isset($_GET['del'])) {
    admin_verify_csrf();
    $delId = (int)$_GET['del'];
    if ($delId > 0 && $delId != $currentUser['id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $delId);
            $stmt->execute();
            $msg = 'Kasutaja kustutatud!';
        }
    }
}

$usersRes = $conn->query("SELECT * FROM users ORDER BY id ASC");
include 'header.php';
?>

<div style="margin-bottom: 25px;">
    <h1 style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;">Kasutajate haldamine</h1>
    <p style="color: var(--text-secondary);">Halda registreeritud kasutajaid ja administraatori õigusi.</p>
</div>

<?php if ($msg): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="admin-table-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>Nimi</th>
                <th>Kasutajanimi</th>
                <th>E-post</th>
                <th>Amet</th>
                <th>Roll</th>
                <th>Reg. kuupäev</th>
                <th style="text-align: right;">Tegevused</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($usersRes && $usersRes->num_rows > 0): ?>
                <?php while ($u = $usersRes->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td><b><?= htmlspecialchars($u['name']) ?></b></td>
                        <td><code><?= htmlspecialchars($u['login']) ?></code></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['job']) ?></td>
                        <td>
                            <span class="user-role-badge role-<?= strtolower($u['status']) ?>">
                                <?= htmlspecialchars($u['status']) ?>
                            </span>
                        </td>
                        <td><?= $u['registratsion_date'] ?></td>
                        <td style="text-align: right;">
                            <div class="action-btns" style="justify-content: flex-end;">
                                <a href="users.php?toggle_role=1&id=<?= $u['id'] ?>&csrf_token=<?= csrf_token() ?>" class="action-btn-edit">
                                    <?= $u['status'] === 'admin' ? 'Tee tavakasutajaks' : 'Määra adminiks' ?>
                                </a>
                                <?php if ($u['id'] != $currentUser['id']): ?>
                                    <span style="color: var(--border-color);">|</span>
                                    <a href="users.php?del=<?= $u['id'] ?>&csrf_token=<?= csrf_token() ?>" class="action-btn-del" onclick="return confirm('Kustuta kasutaja?');">Kustuta</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
