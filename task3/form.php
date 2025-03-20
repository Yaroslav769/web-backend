<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <title>Task 3</title>
</head>

<body>

<form class="content" id="form" action="index.php" method="POST">
  <h2 class="text-center">Форма</h2>
  
  <label for="fio" class="form-label">ФИО:</label>
  <input class="form-control" name="fio" id="fio" required>

  <label for="phone" class="form-label">Телефон: </label>
  <input class="form-control" type="tel" name="phone" id="phone" required />

  <label for="email" class="form-label">Электронная почта:</label>
  <input class="form-control" type="email" name="email" id="email" required />

  <label for="birthday" class="form-label">Дата рождения:</label>
  <input class="form-control" type="date" name="data" id="birthday" required />

  <label class="form-label">Пол:</label>
  <div class="form-check">
    <input class="form-check-input" type="radio" name="sex" value="male" id="male" required />
    <label class="form-check-label" for="male">Мужской</label>
  </div>
  <div class="form-check">
    <input class="form-check-input" type="radio" name="sex" value="female" id="female" required />
    <label class="form-check-label" for="female">Женский</label>
  </div>

  <label class="form-label" for="languages">Любимые языки программирования:</label>
  <select class="form-select" id="languages" name="languages[]" multiple="multiple">
    <option value="1">Pascal</option>
    <option value="2">C</option>
    <option value="3">C++</option>
    <option value="4">JavaScript</option>
    <option value="5">PHP</option>
    <option value="6">Python</option>
    <option value="7">Java</option>
    <option value="8">Haskell</option>
    <option value="9">Clojure</option>
    <option value="10">Prolog</option>
    <option value="11">Scala</option>
  </select>

  <label class="form-label" for="biography">Биография:</label>
  <textarea class="form-control" id="biography" name="biog" required></textarea>

  <div class="form-check">
    <input class="form-check-input" type="checkbox" name="agree" value="yes" id="checkbox" required />
    <label class="form-check-label" for="checkbox">Ознакомлен(а)</label>
  </div>

  <button class="btn btn-primary w-100 mt-2" type="submit">Сохранить</button>
</form>

</body>
</html>

