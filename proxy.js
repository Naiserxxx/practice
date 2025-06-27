const express = require('express');
const fetch = (...args) => import('node-fetch').then(({ default: fetch }) => fetch(...args));

const app = express();
const PORT = 3000;

// CORS middleware
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    next();
});

// Общие заголовки авторизации
const DEFAULT_HEADERS = {
    'Authorization': 'Basic Y2xpOjEyMzQ0MzIx'
};

// Универсальный proxy-обработчик
async function proxyGet(req, res, targetUrl, extraHeaders = {}) {
    try {
        const headers = { ...DEFAULT_HEADERS, ...extraHeaders };
        const response = await fetch(targetUrl, { headers });

        // Для CPU: если ошибка — всегда вернуть { value: 0 }
        if (targetUrl.includes('/cpu/')) {
            if (!response.ok) {
                return res.json({ value: 0 });
            }
            const text = await response.text();
            return res.json({ value: isNaN(Number(text)) ? 0 : Number(text) });
        }

        // Для остальных ручекк:
        if (!response.ok) throw new Error('Bad response: ' + response.status);

        const contentType = response.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            const data = await response.json();
            return res.json(data);
        } else {
            const text = await response.text();
            return res.json({ value: isNaN(Number(text)) ? text : Number(text) });
        }
    } catch (e) {

        if (req.path === '/cpu') {
            return res.json({ value: 0 });
        }
        console.error(e);
        return res.status(500).json({ error: 'Ошибка прокси', details: e.message });
    }
}

// Таблица товаров
app.get('/products', (req, res) =>
    proxyGet(req, res, 'http://exercise.develop.maximaster.ru/service/products/')
);

// CPU загрузка
app.get('/cpu', (req, res) =>
    proxyGet(req, res, 'http://exercise.develop.maximaster.ru/service/cpu/')
);

// Список городов (для калькулятора)
app.get('/cities', (req, res) =>
    proxyGet(req, res, 'http://exercise.develop.maximaster.ru/service/city/')
);

// Калькулятор доставки (city + weight)
app.get('/delivery', (req, res) => {
    const city = encodeURIComponent(req.query.city || '');
    const weight = parseInt(req.query.weight, 10) || 0;
    if (!city || !weight) {
        return res.status(400).json({ status: "error", message: "Некорректные параметры" });
    }
    const url = `http://exercise.develop.maximaster.ru/service/delivery/?city=${city}&weight=${weight}`;
    proxyGet(req, res, url);
});



app.listen(PORT, () => {
    console.log(`Proxy listening on http://localhost:${PORT}`);
});
