<?php
    function returnFail($message) {
        http_response_code(400);
        $response["status"] = "fail";
        $response["message"] = $message;

        echo json_encode($response);
        exit();
    }

    //Set protocol, mismatch causes issues on prod server
    if (isset($_SERVER['HTTPS']) &&
    ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
    $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        $protocol = 'https://';
    }
    else {
        $protocol = 'http://';
    }
?>