<?php   
header('Content-Type: text/html; charset=UTF-8');

$session_started = false;
if (!empty($_COOKIE[session_name()]) && session_start()) {
    $session_started = true;
    if (!empty($_SESSION['login'])) {
        header('Location: index.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    ?>
    <form action="login.php" method="post">
        <input name="login" placeholder="Логин" />
        <input name="pass" placeholder="Пароль" type="password" />
        <input type="submit" value="Войти" />
    </form>
    <?php
} else {
    if (!$session_started) {
        session_start();
    }

    $login = $_POST['login'] ?? '';
    $password = $_POST['pass'] ?? '';

    try {
        $db = new PDO('mysql:host=localhost;dbname=u68765', 'u68765', '9756853', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $stmt = $db->prepare("SELECT id, pass FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && md5($password) === $user['pass']) {
            $_SESSION['login'] = $login;
            $_SESSION['uid'] = $user['id'];

            setcookie('login', $login, time() + 3600);
            setcookie('pass', $password, time() + 3600);

            header('Location: index.php');
            exit();
        } else {
            echo "<p style='color: red;'>Неверный логин или пароль</p>";
            echo '<a href="login.php">Попробовать снова</a>';
        }
    } catch (PDOException $e) {
        print('Ошибка: ' . $e->getMessage());
        exit();
    }
}
?>

