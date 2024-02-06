<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test</title>
</head>
<body>
    <?php
        $currentURL = "http" . ($_SERVER['HTTPS'] ?? "") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $urlParts = parse_url($currentURL);
        $curlURL = $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'];
        $curlURL = str_replace("/index.php", "", $curlURL);
    ?>
    <div style="display: inline-flex;">
        <div>
            <form action="index.php" id="formularz" method="POST">
                <label for="imie">Imię:</label><br>
                <input type="text" id="imie" name="imie"><br>
                <label for="nazwisko">Nazwisko:</label><br>
                <input type="text" id="nazwisko" name="nazwisko"><br><br>
                <button type="submit">Wyślij</button>
                <a href="">Odśwież stronę</a>
            </form>
        </div>
        <div style="padding: 20px;">
            <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $imie = $_POST["imie"];
                    $nazwisko = $_POST["nazwisko"];

                    if (empty($imie) || empty($nazwisko)) {
                        echo "Brak wymaganych danych";
                    }

                    $curlURL .= '/api/serwer.php';
                    $curlURL = str_replace("//", "/", $curlURL);
                    $curlData = array(
                        "imie" => $imie,
                        "nazwisko" => $nazwisko
                    );
                    $curl = curl_init($curlURL);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $curlData);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $apiKey = "moj-klucz-do-api"; 
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                        "Authorization: APIKey:$apiKey"
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);

                    $response = json_decode($response, true);
                    if ($response['message']) {
                        echo $response['message'];
                    }
                    if ($response['error']) {
                        echo $response['error'];
                    }
                }
                /* */
            ?>       
        </div>
    </div>

    <script>
        /*
        document.getElementById("formularz").addEventListener("submit", function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            <?php
            $currentURL = "http" . ($_SERVER['HTTPS'] ?? "") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $urlParts = parse_url($currentURL);
            $urlWithoutParams = $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'];
            echo 'var url = "'.$urlWithoutParams.'api/serwer.php";';
            ?>
            console.log(url);
            const apiKey = "moj-klucz-do-api"; 
            const headers = new Headers();
            headers.append("Authorization", `APIKey:${apiKey}`);
            console.log('ustawiono APIKey');
            fetch(url, {
                method: "POST",
                headers: headers, 
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log(data.message);
                alert(data.message);
            })
            .catch(error => {
                console.error("Błąd:", error);
                alert("Wystąpił błąd podczas wysyłania danych.");
            });
        });
        /* */
    </script>
</body>
</html>
