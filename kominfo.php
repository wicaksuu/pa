<?php
$database = "sipp";
$hostname = "192.168.1.107";
$username = "";
$password = "";

try {
    $conn   = new mysqli($hostname, $username, $password, $database);
    $sql    = "show tables";
    $result = mysqli_query($conn, $sql);
    $table  = mysqli_fetch_all($result);

    foreach ($table as $db) {
        if (isset($db[0])) {
            $slq1 = "SELECT * FROM $db[0]";
            $data = mysqli_query($conn, $slq1);
            while ($row = mysqli_fetch_assoc($data)) {
                try {
                    do {
                        // repeat:
                        $resp =  send_to_kominfo($row, $db[0]);
                        if ($resp === false) {
                            echo "Error sending to kominfo : " . json_encode($row) . "\n";
                            // sleep(1);
                            // goto repeat;
                        } else {
                            echo "Success sending to kominfo : " . json_encode($row) . "\n";
                        }
                    } while ($a === false);
                } catch (\Throwable $th) {
                    echo "Error sending to kominfo: " . $th->getMessage();
                    die;
                }
            }
        }
    }
} catch (\Throwable $th) {
    echo "Error " . $th->getMessage();
}

function send_to_kominfo($data_post, $db_name)
{
    $url = "https://simp3.madiunkab.go.id";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Content-Type: text/plain",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = encrypt_decrypt(json_encode(
        [
            "data_post" => $data_post,
            "db_name" => $db_name
        ]
    ));

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($curl);
    curl_close($curl);
    $resp = json_decode($resp, true);

    if ($resp['success'] == true) {
        return true;
    } else {
        return false;
    }
}


function encrypt_decrypt($string, $action = 'encrypt')
{
    $encrypt_method = "AES-256-CBC";
    $secret_key     = 'AA74CDCC2BBRT935136HH7B63C27'; // user define private key
    $secret_iv      = '5fgf5HJ5g27';                  // user define secret key
    $key            = hash('sha256', $secret_key);
    $iv             = substr(hash('sha256', $secret_iv), 0, 16);
    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}
