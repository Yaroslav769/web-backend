<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit();
}
echo "<h1>Добро пожаловать, Администратор!</h1>";

try {
    $db = new PDO('mysql:host=localhost;dbname=u68765', 'u68765', '9756853', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            echo "Запись удалена!";
        } else {
            echo "Пользователь не найден!";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $id = (int)$_POST['id'];
        $login = $_POST['login'];
        $password = md5($_POST['password']);

        $stmt = $db->prepare("UPDATE users SET login = ?, pass = ? WHERE id = ?");
        $stmt->execute([$login, $password, $id]);
        echo "Запись обновлена!";
    }

    $editData = null;
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $editData = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $stmt = $db->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="admin.css">
    <title>Панель администратора</title>
</head>
<body>
<h2>Управление пользователями</h2>

<?php if ($editData): ?>
    <h3>Редактировать пользователя</h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $editData['id'] ?>">
        Логин: <input type="text" name="login" value="<?= $editData['login'] ?>" required><br>
        Новый пароль: <input type="password" name="password" required><br>
        <button type="submit" name="update">Обновить</button>
    </form>
<?php endif; ?>

<h3>Список пользователей</h3>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Логин</th>
        <th>Действия</th>
    </tr>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= $user['login'] ?></td>
            <td>
                <a href="?action=edit&id=<?= $user['id'] ?>">Редактировать</a>
                <a href="?action=delete&id=<?= $user['id'] ?>" onclick="return confirm('Удалить пользователя?');">Удалить</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<a href="out.php">Выйти</a>
</body>
</html>

