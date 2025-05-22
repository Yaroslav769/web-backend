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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit();
}

try {
    $db = new PDO('mysql:host=localhost;dbname=u68765', 'u68765', '9756853', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF токен не прошел проверку.");
    }

    $id = (int)$_POST['id'];
    $fio = $_POST['fio'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $date1 = $_POST['date1'];
    $sex = $_POST['sex'];
    $biog = $_POST['biog'];
    $language_id = (int)$_POST['language_id'];

    $stmt = $db->prepare("UPDATE form SET fio = ?, phone = ?, email = ?, date1 = ?, sex = ?, biog = ? WHERE id = ?");
    $stmt->execute([$fio, $phone, $email, $date1, $sex, $biog, $id]);

    $stmt = $db->prepare("UPDATE lang_check SET language_id = ? WHERE check_id = ?");
    $stmt->execute([$language_id, $id]);

    header('Location: admin.php');
    exit();
}

    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        $stmt = $db->prepare("SELECT * FROM form WHERE id = ?");
        $stmt->execute([$id]);
        $editData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$editData) {
            echo "Пользователь не найден!";
            exit();
        }

        $stmt = $db->prepare("SELECT language_id FROM lang_check WHERE check_id = ?");
        $stmt->execute([$id]);
        $languageData = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentLanguageId = $languageData ? $languageData['language_id'] : null;

        $stmt = $db->query("SELECT id, name FROM languages");
        $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        echo "ID пользователя не указан!";
        exit();
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "Произошла ошибка. Попробуйте позже.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="admin.css">
    <title>Редактирование пользователя</title>
</head>
<body>
<h2>Редактировать пользователя: <?= htmlspecialchars($editData['fio']) ?></h2>

<form method="POST" action="edit_user.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="id" value="<?= $editData['id'] ?>">

    ФИО: <input type="text" name="fio" value="<?= htmlspecialchars($editData['fio']) ?>" required><br>
    Телефон: <input type="text" name="phone" value="<?= htmlspecialchars($editData['phone']) ?>" required><br>
    Email: <input type="email" name="email" value="<?= htmlspecialchars($editData['email']) ?>" required><br>
    Дата рождения: <input type="date" name="date1" value="<?= $editData['date1'] ?>" required><br>
    Пол:
    <select name="sex">
        <option value="male" <?= $editData['sex'] == 'male' ? 'selected' : '' ?>>Мужской</option>
        <option value="female" <?= $editData['sex'] == 'female' ? 'selected' : '' ?>>Женский</option>
    </select><br>
    Биография: <textarea name="biog" required><?= htmlspecialchars($editData['biog']) ?></textarea><br>

    <label for="language_id">Любимый язык программирования:</label>
    <select name="language_id" required>
        <?php foreach ($languages as $language): ?>
            <option value="<?= $language['id'] ?>" <?= $currentLanguageId == $language['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($language['name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <button type="submit" name="update">Обновить</button>
    <a href="admin.php">Отмена</a>
</form>
</body>
</html>

