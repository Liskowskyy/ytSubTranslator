<?php
    function returnFail($message) {
        $response["status"] = "fail";
        $response["message"] = $message;

        echo json_encode($response);
        exit();
    }
?>