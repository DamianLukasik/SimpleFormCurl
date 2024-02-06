<?php

function komunikat($text, $header = null) {
    switch ($header) {
        case 400:
            header("HTTP/1.1 400 Bad Request");
            break;
        case 401:
            header("HTTP/1.1 401 Unauthorized");
            break;
        case 500:
            header("HTTP/1.1 500 Internal Server Error");
            break;
        default:
            header("HTTP/1.1 200 OK");
            break;
    }
    exit(json_encode($text));
}

// Autoryzacja
$headers = getallheaders();
if (isset($headers['Authorization'])) {
    $serverApiKey = 'moj-klucz-do-api';
    $clientApiKey = '';
    $authHeader = $headers['Authorization'];
    $parts = explode(':', $authHeader);
    if (count($parts) === 2 && strtolower($parts[0]) === 'apikey') {
        $clientApiKey = $parts[1];
    }
    if ($clientApiKey !== $serverApiKey) {
        komunikat(["error" => "Nieautoryzowany dostęp"], 401);
    }
} else {
    komunikat(["error" => "Nieautoryzowany dostęp"], 401);
}

// Połączenie z bazą danych
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "test";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Błąd połączenia z bazą danych.");
    }
} catch (Exception $e) {
    komunikat(["error" => "Błąd połączenia z bazą danych."], 500);
}

// Obsługa żądania API
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Pobranie i walidacja danych
    $imie = $_POST["imie"] ?? null;
    $nazwisko = $_POST["nazwisko"] ?? null;
    if (empty($imie) || empty($nazwisko)) {
        komunikat(["error" => "Brak wymaganych danych"], 400);
    }
    if (!preg_match('/^[a-zA-ZęóąśłżźćńĘÓĄŚŁŻŹĆŃ]+$/', $imie) || !preg_match('/^[a-zA-ZęóąśłżźćńĘÓĄŚŁŻŹĆŃ]+$/', $nazwisko)) {
        komunikat(["error" => "Imię i nazwisko mogą zawierać tylko litery"], 400);
    }
    // Zapis danych do bazy danych
    $sql = "INSERT INTO user (uuid, imie, nazwisko) VALUES (UUID(), ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $imie, $nazwisko);

    if ($stmt->execute()) {
        komunikat(["message" => "Dane zostały pomyślnie zapisane"]);
    } else {
        komunikat(["error" => "Błąd podczas zapisywania danych do bazy"], 500);
    }
    $stmt->close();
}
$conn->close();
