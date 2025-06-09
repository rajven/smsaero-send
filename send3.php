<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/sms.php';

/**
 * Отправка SMS-сообщения на указанный номер.
 *
 * @param string $phone Номер телефона получателя (в любом формате).
 * @param string $text Текст сообщения.
 * @return array|int Возвращает массив со статусом отправки или 0 в случае ошибки.
 */
function send_sms($phone, $text) {
    // Очищаем номер телефона от всех символов, кроме цифр
    $phone = preg_replace('~\D+~', '', $phone);

    if (!preg_match('/^[0-9]{10,13}$/', $phone)) {
        return 0;
    }

    $len = strlen($phone);

    // Преобразуем к международному формату
    if ($len === 10) {
        $phone = '7' . $phone;
    } elseif ($len === 11 && preg_match('/^89/', $phone)) {
        $phone = preg_replace('/^8/', '7', $phone);
    } elseif ($len < 10 || $len > 13) {
        return 0;
    }

    return Sms::send($phone, $text);
}

/**
 * Получает параметр из POST или GET (с приоритетом POST).
 *
 * @param string $key Название параметра.
 * @return string|null
 */
function get_request_param($key) {
    return filter_input(INPUT_POST, $key) ?? filter_input(INPUT_GET, $key);
}

// Получаем параметры запроса
$phone = get_request_param('to');
$sms_text = get_request_param('text');

// Если оба параметра присутствуют
if (!empty($phone) && !empty($sms_text)) {
    $result = send_sms($phone, $sms_text);

    if (!empty($result) && is_array($result)) {
        foreach ($result as $recipient => $statusJson) {
            $status = json_decode($statusJson, true, 512, JSON_OBJECT_AS_ARRAY);
            if (isset($status["data"]["id"], $status["data"]["status"])) {
                echo htmlspecialchars($recipient) . ':' .
                     htmlspecialchars($status["data"]["id"]) . ':' .
                     htmlspecialchars($status["data"]["status"]) . '<br>';
            }
        }
    }
}

// Очистка переменных (необязательно, но на всякий случай)
unset($_GET, $_POST);
?>
