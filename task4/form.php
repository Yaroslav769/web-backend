<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Task 4</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<?php
if (!empty($messages)) {
    echo '<div id="messages">';
    foreach ($messages as $message) {
        echo $message;
    }
    echo '</div>';
}
?>

<form class="content" id="form" action="index.php" method="POST">
    <h2 class="text-center">Форма</h2>

    <label for="fio" class="form-label">ФИО:</label>
    <input class="form-control <?php if ($errors['fio']) echo 'error'; ?>" name="fio" id="fio"
           value="<?php echo htmlspecialchars($values['fio']); ?>" required>

    <label for="phone" class="form-label">Телефон: </label>
    <input class="form-control <?php if ($errors['phone']) echo 'error'; ?>" type="tel" name="phone" id="phone"
           value="<?php echo htmlspecialchars($values['phone']); ?>" required />

    <label for="email" class="form-label">Электронная почта:</label>
    <input class="form-control <?php if ($errors['email']) echo 'error'; ?>" type="email" name="email" id="email"
           value="<?php echo htmlspecialchars($values['email']); ?>" required />

    <label for="birthday" class="form-label">Дата рождения:</label>
    <input class="form-control <?php if ($errors['data']) echo 'error'; ?>" type="date" name="data" id="birthday"
           value="<?php echo htmlspecialchars($values['data']); ?>" required />

    <label class="form-label">Пол:</label>
    <div class="form-check">
        <input class="form-check-input <?php if ($errors['sex']) echo 'error'; ?>" type="radio" name="sex" value="male" id="male"
               <?php if ($values['sex'] === 'male') echo 'checked'; ?> required />
        <label class="form-check-label" for="male">Мужской</label>
    </div>
    <div class="form-check">
        <input class="form-check-input <?php if ($errors['sex']) echo 'error'; ?>" type="radio" name="sex" value="female" id="female"
               <?php if ($values['sex'] === 'female') echo 'checked'; ?> required />
        <label class="form-check-label" for="female">Женский</label>
    </div>

    <label class="form-label" for="languages">Любимые языки программирования:</label>
    <select class="form-select <?php if ($errors['languages']) echo 'error'; ?>" id="languages" name="languages[]" multiple>
        <?php
        $lang_options = [
            1 => 'Pascal', 2 => 'C', 3 => 'C++', 4 => 'JavaScript',
            5 => 'PHP', 6 => 'Python', 7 => 'Java', 8 => 'Haskell',
            9 => 'Clojure', 10 => 'Prolog', 11 => 'Scala'
        ];
        $selected_langs = explode(',', $values['languages']);
        foreach ($lang_options as $val => $label) {
            printf('<option value="%d" %s>%s</option>', $val,
                in_array($val, $selected_langs) ? 'selected' : '', $label);
        }
        ?>
    </select>

    <label class="form-label" for="biography">Биография:</label>
    <textarea class="form-control <?php if ($errors['biog']) echo 'error'; ?>" id="biography" name="biog" required><?php echo $values['biog']; ?></textarea>

    <div class="form-check">
        <input class="form-check-input <?php if ($errors['agree']) echo 'error'; ?>" type="checkbox" name="agree" value="yes" id="checkbox"
               <?php if (!empty($values['agree'])) echo 'checked'; ?> required />
        <label class="form-check-label" for="checkbox">Ознакомлен(а)</label>
    </div>

    <button class="btn" type="submit">Сохранить</button>
</form>

</body>
</html>
