<?php
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['save'])) {
        print('Спасибо, результаты сохранены.');
    }
    include('form.php');
    exit();
}

$errorMessages = [];
$errors = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['fio']) || !preg_match("/^[\p{L} ]+$/u", trim($_POST['fio'])) || strlen(trim($_POST['fio'])) > 150) {
        $errorMessages[] = empty($_POST['fio']) ? "Укажите ФИО.<br>" : "Неправильная запись ФИО.<br>";
        $errors = true;
    } else {
        $name = trim($_POST['fio']);
    }

    if (empty($_POST['phone']) || !preg_match("/^\+?[8][0-9]{10}$/", $_POST['phone'])) {
        $errorMessages[] = empty($_POST['phone']) ? "Укажите номер телефона.<br>" : "Неправильная запись номера телефона.<br>";
        $errors = true;
    } else {
        $phone = $_POST['phone'];
    }

    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errorMessages[] = empty($_POST['email']) ? "Укажите email.<br>" : "Неправильная запись email.<br>";
        $errors = true;
    } else {
        $email = $_POST['email'];
    }

    if (empty($_POST['data']) || !($data = DateTime::createFromFormat('Y-m-d', $_POST['data'])) || $data->format('Y-m-d') !== $_POST['data']) {
        $errorMessages[] = empty($_POST['data']) ? "Дата не выбрана.<br>" : "Неправильная запись даты.<br>";
        $errors = true;
    } else {
        $data1 = $_POST['data'];
    }

    if (empty($_POST['sex']) || !in_array($_POST['sex'], ['male', 'female'])) {
        $errorMessages[] = empty($_POST['sex']) ? "Пол не выбран.<br>" : "Неправильный выбор пола.<br>";
        $errors = true;
    } else {
        $sex = $_POST['sex'];
    }

    if (empty($_POST['languages'])) {
        $errorMessages[] = "Вы не выбрали ни одного языка программирования.<br>";
        $errors = true;
    }

    if (empty(trim($_POST['biog']))) {
        $errorMessages[] = "Биография пуста.<br>";
        $errors = true;
    } else {
        $biog = trim($_POST['biog']);
    }

    if (($_POST['agree'] ?? '') === 'yes') {
        $sogl = 1;
    } else {
        $errorMessages[] = "Подтвердите ознакомление с контрактом.<br>";
        $errors = true;
    }

    if (!empty($errorMessages)) {
        echo '<div style="color: red;">' . implode('', $errorMessages) . '</div>';
        exit();
    }

    $user ='u68765';
    $pass='9756853';
    $db = new PDO('mysql:host=localhost;dbname=u68765', $user, $pass,
  [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  try {
        $stmt = $db->prepare("INSERT INTO form (fio, phone, email, date1, sex, biog, sogl) 
                              VALUES (:fio, :phone, :email, :date1, :sex, :biog, :sogl)");
        $stmt->execute([
            'fio' => $name,
            'phone' => $phone,
            'email' => $email,
            'date1' => $data1,
            'sex' => $sex,
            'biog' => $biog,
            'sogl' => $sogl
        ]);

        $form_id = $db->lastInsertId();

        if (!empty($_POST['languages'])) {
            $stmt = $db->prepare("INSERT INTO lang_check (check_id, language_id) VALUES (:check_id, :language_id)");
            foreach ($_POST['languages'] as $language_id) {
                if (ctype_digit($language_id)) {
                    $stmt->execute(['check_id' => $form_id, 'language_id' => $language_id]);
                }
            }
        }

        echo "Результаты сохранены.";
    } catch (PDOException $e) {
        echo 'Ошибка: ' . $e->getMessage();
        exit();
    }
}
