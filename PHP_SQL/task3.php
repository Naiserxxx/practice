<?php
define('CITIES_CACHE', __DIR__ . '/cities_cache.json');
define('CITIES_API', 'http://localhost:3000/cities');
define('DELIVERY_API', 'http://localhost:3000/delivery');



// --- Запрос городов ---
if (isset($_GET['action']) && $_GET['action'] === 'cities') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(getCities());
    exit;
}

// --- Запрос расчёта доставки ---
if (isset($_POST['action']) && $_POST['action'] === 'delivery') {
    header('Content-Type: application/json; charset=utf-8');
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $weight = isset($_POST['weight']) ? intval($_POST['weight']) : 0;

    if (!$city) {
        echo json_encode(['status'=>'error', 'message'=>'Не выбран город']);
        exit;
    }
    if ($weight < 1) {
        echo json_encode(['status'=>'error', 'message'=>'Вес должен быть положительным числом']);
        exit;
    }

    $url = DELIVERY_API . '?city=' . urlencode($city) . '&weight=' . intval($weight);
    $result = @file_get_contents($url);
    if ($result === false) {
        echo json_encode(['status'=>'error', 'message'=>'Ошибка запроса к сервису доставки']);
        exit;
    }
    $data = @json_decode($result, true);
    if (!$data || !isset($data['status'])) {
        echo json_encode(['status'=>'error', 'message'=>'Некорректный ответ сервиса доставки']);
        exit;
    }
    echo json_encode($data);
    exit;
}

function getCities() {
    if (file_exists(CITIES_CACHE)) {
        $stat = stat(CITIES_CACHE);
        $fileDay = date('Y-m-d', $stat['mtime']);
        $today = date('Y-m-d');
        if ($fileDay == $today) {
            $json = file_get_contents(CITIES_CACHE);
            $data = @json_decode($json, true);
            if ($data && is_array($data)) {
                return $data;
            }
        }
    }
    // Обновляем кэш
    $cities = @file_get_contents(CITIES_API);
    $data = @json_decode($cities, true);
    if ($data && is_array($data)) {
        file_put_contents(CITIES_CACHE, json_encode($data));
        return $data;
    }
    // Если не удалось — возвращаем из кэша если есть
    if (file_exists(CITIES_CACHE)) {
        $json = file_get_contents(CITIES_CACHE);
        $data = @json_decode($json, true);
        if ($data && is_array($data)) return $data;
    }
    return array('Москва');
}
