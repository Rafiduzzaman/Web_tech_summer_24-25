<?php
if (isset($_GET['page']) && $_GET['page'] === 'login') {
    include __DIR__ . '/../view/auth/login.php';
    exit;
}
?>

<?php
if (isset($_GET['page'])) {
    switch ($_GET['page']) {
        case 'login':
            include __DIR__ . '/../view/auth/login.php';
            exit;
        case 'register':
            include __DIR__ . '/../view/auth/register.php';
            exit;
    }
}
?>
