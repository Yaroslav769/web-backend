CREATE TABLE IF NOT EXISTS `form` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fio` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(15) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `date1` DATE NOT NULL,
    `sex` ENUM('male', 'female') NOT NULL,
    `biog` TEXT NOT NULL,
    `sogl` TINYINT(1) NOT NULL
);

CREATE TABLE IF NOT EXISTS `lang_check` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `check_id` INT NOT NULL,
    `language_id` INT NOT NULL,
    FOREIGN KEY (`check_id`) REFERENCES `form`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `languages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL
);

INSERT INTO `languages` (`name`) VALUES
('Pascal'),
('C'),
('C++'),
('JavaScript'),
('PHP'),
('Python'),
('Java'),
('Haskell'),
('Clojure'),
('Prolog'),
('Scala');
