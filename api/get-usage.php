<?php
    //Set content type
    header('Content-Type: application/json; charset=utf-8');

    //Set Composer root
    require_once(__DIR__."/../vendor/autoload.php");

    //Load config
    require("config.php");

    $apiKey = $DeepLAPIKey; //Get key from config.php

    $deepl = new \DeepL\Translator($apiKey);

    //Enclose in try to return an error if API Key is invalid
    try {
        $usage = $deepl->getUsage(); //Get usage

        if ($usage->character) {
            //Get stats from DeepL
            $charactersUsed = $usage->character->count;
            $characterLimit = $usage->character->limit;
            $limitReached = $usage->anyLimitReached();

            //Return stats
            $response["status"] = "success";
            $response["data"]["charactersUsed"] = $charactersUsed;
            $response["data"]["characterLimit"] = $characterLimit;
            $response["data"]["limitReached"] = $limitReached;

            echo json_encode($response);
        }
    }
    catch(Exception $e) {
        //Get error message and return it
        $response["status"] = "error";
        $response["message"] = $e->getMessage();

        echo json_encode($response);
    }


?>