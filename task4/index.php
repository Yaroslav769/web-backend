<?php
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = [];

    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = 'Спасибо, данные сохранены.';
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

    include('form.php');
    exit();
} else {
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
        $valid_languages = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        $invalid_languages = [];

        foreach ($_POST['languages'] as $lang_id) {
            if (!in_array($lang_id, $valid_languages)) {
                $invalid_languages[] = $lang_id;
            }
        }

        if (!empty($invalid_languages)) {
            setcookie('languages_error', '1', time() + 24 * 60 * 60);
            $errors = TRUE;
        } else {
            setcookie('languages_value', implode(',', $_POST['languages']), time() + 30 * 24 * 60 * 60);
        }
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

    } catch (PDOException $e) {
        print('Ошибка: ' . $e->getMessage());
        exit();
    }

    setcookie('save', '1');
    header('Location: index.php');
}
?>
