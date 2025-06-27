<?php
// Файл для хранения сообщений
$csvFile = __DIR__ . '/guestbook.csv';

// === Добавление сообщения ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $msg  = isset($_POST['message']) ? trim($_POST['message']) : '';
    if ($msg !== '') {
        if ($name === '') $name = 'Анонимно';
        $name = htmlspecialchars($name);
        $msg  = htmlspecialchars($msg);
        $date = date('d.m.Y H:i');
        // Запись в CSV
        $row = [$date, $name, $msg];
        $f = fopen($csvFile, 'a+');
        fputcsv($f, $row, ';');
        fclose($f);
        header("Location: " . $_SERVER['REQUEST_URI']); // PRG
        exit();
    }
}

// === Чтение сообщений ===
$messages = [];
if (file_exists($csvFile)) {
    $f = fopen($csvFile, 'r');
    while (($row = fgetcsv($f, 1000, ';')) !== false) {
        $messages[] = $row;
    }
    fclose($f);
}
// Показываем новые сверху
$messages = array_reverse($messages);
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Гостевая книга</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #fafbfc; font-family: Arial, sans-serif; margin: 0; }
        .container { max-width: 500px; margin: 36px auto 0 auto; background: #fff; padding: 22px 14px 30px 14px; }
        h1 { font-size: 2rem; font-weight: 700; margin-bottom: 20px; text-align: center; }
        .msg-list { margin-bottom: 28px; }
        .msg { border: 1px solid #333; margin-bottom: 12px; padding: 10px 12px; }
        .meta { font-size: 0.98rem; color: #555; display: flex; justify-content: space-between; margin-bottom: 4px;}
        .msg-text { font-size: 1.07rem; }
        form { display: flex; flex-direction: column; gap: 12px;}
        input[type="text"], textarea { font-size: 1.06rem; padding: 7px; border: 1px solid #aaa; width: 100%; }
        textarea { min-height: 56px; resize: vertical; }
        button { font-size: 1.09rem; padding: 8px 0; background: #ececec; border: 1px solid #555; cursor: pointer;}
        button:hover { background: #f7f7f7;}
        @media (max-width: 600px) {
            .container { max-width: 99vw; margin: 10vw 0 0 0;}
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Гостевая книга</h1>
    <div class="msg-list">
        <?php if ($messages): foreach($messages as $msg): ?>
            <div class="msg">
                <div class="meta">
                    <span><?= $msg[0] ?></span>
                    <b><?= $msg[1] ?></b>
                </div>
                <div class="msg-text"><?= nl2br($msg[2]) ?></div>
            </div>
        <?php endforeach; endif; ?>
    </div>
    <form method="post" autocomplete="off">
        <input type="text" name="name" placeholder="Имя">
        <textarea name="message" placeholder="Ваше сообщение" required></textarea>
        <button type="submit">Отправить</button>
    </form>
</div>
</body>
</html>
