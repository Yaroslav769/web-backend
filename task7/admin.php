<?php
session_start();

$allowed_includes = ['form.php', 'login_form.php'];

if (isset($_GET['page'])) {
    $page = basename($_GET['page']);
    if (in_array($page, $allowed_includes) && file_exists(__DIR__ . '/' . $page)) {
        include __DIR__ . '/' . $page;
    } else {
        echo "Ошибка: доступ к файлу запрещен.";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Ошибка проверки CSRF токена.');
    }

    $deleteId = (int)$_POST['id'];

    try {
        $db = new PDO('mysql:host=localhost;dbname=u68765', 'u68765', '9756853', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$deleteId]);

        header('Location: admin.php');
        exit();

    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo "Ошибка при удалении пользователя.";
        exit();
    }
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit();
}

echo "<h1>Добро пожаловать, Администратор!</h1>";

try {
    $db = new PDO('mysql:host=localhost;dbname=u68765', 'u68765', '9756853', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $db->prepare("SELECT users.id, users.form_id, login, fio, phone, email, date1, sex, biog 
                          FROM users 
                          JOIN form ON users.form_id = form.id");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT check_id, language_id FROM lang_check");
    $stmt->execute();
    $user_languages = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $user_languages[$row['check_id']] = $row['language_id'];
    }

    $stmt = $db->prepare("SELECT id, name FROM languages");
    $stmt->execute();
    $languages = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $languages[$row['id']] = $row['name'];
    }

    $stmt = $db->prepare("SELECT languages.name, COUNT(*) AS count 
                          FROM lang_check 
                          JOIN languages ON lang_check.language_id = languages.id 
                          GROUP BY languages.name");
    $stmt->execute();
    $lang_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "Произошла ошибка. Попробуйте позже.";
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

<h3>Список пользователей</h3>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Логин</th>
        <th>ФИО</th>
        <th>Телефон</th>
        <th>Email</th>
        <th>Дата рождения</th>
        <th>Пол</th>
        <th>Биография</th>
        <th>Любимый язык программирования</th>
        <th>Действия</th>
    </tr>
    <?php foreach ($users as $user): ?>
        <tr>
	    <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['login']) ?></td>
            <td><?= htmlspecialchars($user['fio']) ?></td>
	    <td><?= htmlspecialchars($user['phone']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['date1']) ?></td>
            <td><?= htmlspecialchars($user['sex']) ?></td>
            <td><?= htmlspecialchars($user['biog']) ?></td>
            <td>
                <?php 
                $language_id = isset($user_languages[$user['form_id']]) ? $user_languages[$user['form_id']] : null;
                echo $language_id ? $languages[$language_id] : 'Не указан';
                ?>
            </td>
            <td>
                <a href="edit_user.php?id=<?= $user['form_id'] ?>">Редактировать</a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить пользователя?');">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <button type="submit">Удалить</button>
		</form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Статистика по языкам программирования</h2>
<table border="1">
    <tr>
        <th>Язык</th>
        <th>Количество пользователей</th>
    </tr>
    <?php foreach ($lang_stats as $stat): ?>
        <tr>
            <td><?= $stat['name'] ?></td>
            <td><?= $stat['count'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<a href="out.php">Выйти</a>
</body>
</html>

