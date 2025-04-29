<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = [];

    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);

        if (!empty($_COOKIE['pass'])) {
            $messages[] = 'Спасибо, результаты сохранены.';
            $messages[] = sprintf(
                'Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong> для изменения данных.',
                strip_tags($_COOKIE['login']),
                strip_tags($_COOKIE['pass'])
            );
            setcookie('login', '', 100000);
            setcookie('pass', '', 100000);
        }
    }

    $errors = [];
    $fields = ['fio', 'phone', 'email', 'data', 'sex', 'languages', 'biog', 'agree'];
    foreach ($fields as $field) {
        $errors[$field] = !empty($_COOKIE[$field . '_error']);
        if ($errors[$field]) {
            setcookie($field . '_error', '', 100000);
            $messages[] = "<div class='error'>Ошибка в поле $field.</div>";
        }
    }

    $values = [];
    foreach ($fields as $field) {
        $values[$field] = $_COOKIE[$field . '_value'] ?? '';
    }

    if (!empty($_SESSION['login'])) {
        try {
            $db = new PDO('mysql:host=localhost;dbname=u68765', 'u68765', '9756853', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $stmt = $db->prepare("SELECT * FROM form WHERE id = (SELECT form_id FROM users WHERE login = ?)");
            $stmt->execute([$_SESSION['login']]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userData) {
                $values['fio'] = $userData['fio'];
                $values['phone'] = $userData['phone'];
                $values['email'] = $userData['email'];
                $values['data'] = $userData['date1'];
                $values['sex'] = $userData['sex'];
                $values['biog'] = $userData['biog'];

                $stmtLang = $db->prepare("SELECT language_id FROM lang_check WHERE check_id = ?");
                $stmtLang->execute([$userData['id']]);
                $langs = $stmtLang->fetchAll(PDO::FETCH_COLUMN);
                $values['languages'] = implode(',', $langs);

                $values['agree'] = 'yes';
            }
        } catch (PDOException $e) {
            print('Ошибка: ' . $e->getMessage());
            exit();
        }
    }

    include('form.php');
    exit();
}

$errors = FALSE;

if (empty($_POST['fio']) || !preg_match('/^[\p{L} ]+$/u', $_POST['fio'])) {
    setcookie('fio_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
}
setcookie('fio_value', $_POST['fio'], time() + 30 * 24 * 60 * 60);

if (empty($_POST['phone']) || !preg_match('/^\+?[8][0-9]{10}$/', $_POST['phone'])) {
    setcookie('phone_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
}
setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 60 * 60);

if (empty($_POST['email']) || !preg_match('/^[^@]+@[^@]+\.[a-z]{2,}$/i', $_POST['email'])) {
    setcookie('email_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
}
setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);

if (empty($_POST['data']) || !DateTime::createFromFormat('Y-m-d', $_POST['data'])) {
    setcookie('data_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
}
setcookie('data_value', $_POST['data'], time() + 30 * 24 * 60 * 60);

if (empty($_POST['sex']) || !in_array($_POST['sex'], ['male', 'female'])) {
    setcookie('sex_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
}
setcookie('sex_value', $_POST['sex'], time() + 30 * 24 * 60 * 60);

if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
    setcookie('languages_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    $valid_languages = [1,2,3,4,5,6,7,8,9,10,11];
    foreach ($_POST['languages'] as $lang_id) {
        if (!in_array($lang_id, $valid_languages)) {
            setcookie('languages_error', '1', time() + 24 * 60 * 60);
            $errors = TRUE;
            break;
        }
    }
    setcookie('languages_value', implode(',', $_POST['languages']), time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['biog']) || !preg_match('/^[\p{L}0-9\s.,!?()\[\]"\-]+$/u', $_POST['biog'])) {
    setcookie('biog_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
}
setcookie('biog_value', $_POST['biog'], time() + 30 * 24 * 60 * 60);

if (empty($_POST['agree'])) {
    setcookie('agree_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('agree_value', 'yes', time() + 30 * 24 * 60 * 60);
}

if ($errors) {
    header('Location: index.php');
    exit();
}

foreach (['fio', 'phone', 'email', 'data', 'sex', 'languages', 'biog', 'agree'] as $field) {
    setcookie($field . '_error', '', 100000);
}

try {
    $db = new PDO('mysql:host=localhost;dbname=u68765', 'u68765', '9756853', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    if (!empty($_SESSION['login'])) {
        $stmt = $db->prepare("SELECT form_id FROM users WHERE login = ?");
        $stmt->execute([$_SESSION['login']]);
        $form_id = $stmt->fetchColumn();

        if ($form_id) {
            $stmt = $db->prepare("UPDATE form SET fio=:fio, phone=:phone, email=:email, date1=:date1, sex=:sex, biog=:biog WHERE id=:id");
            $stmt->execute([
                'fio' => $_POST['fio'],
                'phone' => $_POST['phone'],
                'email' => $_POST['email'],
                'date1' => $_POST['data'],
                'sex' => $_POST['sex'],
                'biog' => $_POST['biog'],
                'id' => $form_id
            ]);

            $stmtLang = $db->prepare("DELETE FROM lang_check WHERE check_id = ?");
            $stmtLang->execute([$form_id]);

            $stmtLangInsert = $db->prepare("INSERT INTO lang_check (check_id, language_id) VALUES (:check_id, :language_id)");
            foreach ($_POST['languages'] as $lang_id) {
                if (ctype_digit($lang_id)) {
                    $stmtLangInsert->execute(['check_id' => $form_id, 'language_id' => $lang_id]);
                }
            }
        }
    } else {
        $stmt = $db->prepare("INSERT INTO form (fio, phone, email, date1, sex, biog, sogl) 
                              VALUES (:fio, :phone, :email, :date1, :sex, :biog, :sogl)");
        $stmt->execute([
            'fio' => $_POST['fio'],
            'phone' => $_POST['phone'],
            'email' => $_POST['email'],
            'date1' => $_POST['data'],
            'sex' => $_POST['sex'],
            'biog' => $_POST['biog'],
            'sogl' => 1
        ]);

        $form_id = $db->lastInsertId();

        $stmtLang = $db->prepare("INSERT INTO lang_check (check_id, language_id) VALUES (:check_id, :language_id)");
        foreach ($_POST['languages'] as $lang_id) {
            if (ctype_digit($lang_id)) {
                $stmtLang->execute(['check_id' => $form_id, 'language_id' => $lang_id]);
            }
        }

        $login = 'user' . rand(1000, 9999);
        $pass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $pass_hash = md5($pass);

        $stmtUser = $db->prepare("INSERT INTO users (form_id, login, pass) VALUES (:form_id, :login, :pass)");
        $stmtUser->execute([
            'form_id' => $form_id,
            'login' => $login,
            'pass' => $pass_hash
        ]);

        setcookie('login', $login, time() + 60);
        setcookie('pass', $pass, time() + 60);
    }
} catch (PDOException $e) {
    print('Ошибка: ' . $e->getMessage());
    exit();
}

setcookie('save', '1');
header('Location: index.php');
?>

<?php if (!empty($_SESSION['login'])): ?>
    <form action="out.php" method="post">
        <button type="submit">Выйти</button>
    </form>
<?php endif; ?>
 
