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
    
    $stmt = $db->query("SELECT users.id, users.form_id, login, fio, phone, email, date1, sex, biog 
                        FROM users 
                        JOIN form ON users.form_id = form.id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->query("SELECT check_id, language_id FROM lang_check");
    $user_languages = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $user_languages[$row['check_id']] = $row['language_id'];
    }

    $stmt = $db->query("SELECT id, name FROM languages");
    $languages = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $languages[$row['id']] = $row['name'];
    }

    $stmt = $db->query("SELECT languages.name, COUNT(*) AS count 
                        FROM lang_check 
                        JOIN languages ON lang_check.language_id = languages.id 
                        GROUP BY languages.name");
    $lang_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <td><?= $user['id'] ?></td>
            <td><?= $user['login'] ?></td>
            <td><?= $user['fio'] ?></td>
            <td><?= $user['phone'] ?></td>
            <td><?= $user['email'] ?></td>
            <td><?= $user['date1'] ?></td>
            <td><?= $user['sex'] ?></td>
            <td><?= $user['biog'] ?></td>
            <td>
                <?php 
                $language_id = isset($user_languages[$user['form_id']]) ? $user_languages[$user['form_id']] : null;
                echo $language_id ? $languages[$language_id] : 'Не указан';
                ?>
            </td>
            <td>
                <a href="edit_user.php?id=<?= $user['form_id'] ?>">Редактировать</a>
                <a href="?action=delete&id=<?= $user['id'] ?>" onclick="return confirm('Удалить пользователя?');">Удалить</a>
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

