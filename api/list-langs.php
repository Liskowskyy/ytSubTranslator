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
        $sourceLanguages = $deepl->getSourceLanguages(); //Get source langs from API
        $targetLanguages = $deepl->getTargetLanguages(); //Get targets langs from API

        //Return list
        $response["status"] = "success";
        $response["data"]["sourceLanguages"] = $sourceLanguages;
        $response["data"]["targetLanguages"] = $targetLanguages;

        echo json_encode($response);
        
    }
    catch(Exception $e) {
        //Get error message and return it
        $response["status"] = "error";
        $response["message"] = $e->getMessage();

        echo json_encode($response);
    }


?>