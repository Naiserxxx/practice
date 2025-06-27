<?php
// Файл, где будет храниться количество посещений
$filename = 'hits.csv';

// Попробуем прочитать текущее количество посещений из файла
if (file_exists($filename)) {
    $count = (int)file_get_contents($filename);
} else {
    $count = 0;
}

// Увеличиваем на 1 (каждый раз при заходе)
$count++;

// Сохраняем новое значение обратно в файл
file_put_contents($filename, $count, LOCK_EX);

// Текущее время по серверу (часы:минуты)
$time = date('H:i');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Счетчик посещений</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #fafbfc; margin:0; }
        .center-box {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; height: 95vh;
        }
        .msg {
            font-size: 1.7rem; background: #fff;
            border-radius: 12px; box-shadow: 0 6px 22px #0001;
            padding: 38px 24px; margin: 18px 0;
        }
    </style>
</head>
<body>
<div class="center-box">
    <div class="msg">
        Страница была загружена <b><?= $count ?></b> раз.<br>
        Текущее время <?= $time ?>.
    </div>
</div>
</body>
</html>
