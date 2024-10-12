<?php
    //Set content type
    header('Content-Type: application/json; charset=utf-8');

    //Set Composer root
    require_once(__DIR__."/../vendor/autoload.php");

    //Load config
    require("config.php");

    //Check for required params
    require("common.php");

    if(empty($_POST["targets"])) {
        returnFail("Missing targets param or is empty");
    }
    if(!isset($_POST["source"])) {
        returnFail("Missing source param");
    }
    if(!isset($_FILES['subtitleFile'])) {
        returnFail("Missing subtitles file");
    }

    $apiKey = $DeepLAPIKey; //Get key from config.php

    $deepl = new \DeepL\Translator($apiKey);

    //List of DeepL supported langs, ignore backwards-compatible unspecified variants in target langs
    $json = file_get_contents("http://{$_SERVER['HTTP_HOST']}/api/list-langs.php");
    $langs = json_decode($json);
    //Only get codes
    $supportedSourceLangsCodes = array_column($langs->data->sourceLanguages, "code");
    $supportedTargetLangsCodes = array_column($langs->data->targetLanguages, "code");

    //Get target langs and filter it
    $targets = $_POST["targets"];
    $targets = explode(',', $targets); //Comma delimited values => array
    $targets = array_unique($targets); //Remove duplicates
    $targets = array_intersect($supportedTargetLangsCodes, $targets); //Remove non-whitelisted values

    //Re-check targets after filtering
    if(empty($targets)) {
        returnFail("No valid target languages");
    }

    //Get source lang and check if it exists
    $source = $_POST["source"];
    if ($source == "") $source = null; //Empty string = null which is auto-detect
    if(!in_array($source, $supportedSourceLangsCodes)) {
        returnFail("Source language invalid");
    }

    $formality = "default"; //Formality default if nothing entered in params

    //Get formality if entered, leave on default if not set properly/left on default
    if(isset($_POST["formality"])) {
        if($_POST["formality"] == "formal") $formality = "prefer_more";
        else if($_POST["formality"] == "informal") $formality = "prefer_less";
    }

    //Check size
    if($_FILES['subtitleFile']['size'] > $maxUploadSize) {
        returnFail("Subtitle file too big");
    }

    //Check if file type is allowed
    $allowed = array('srt', 'vtt', 'sbv');
    $uploadName = $_FILES['subtitleFile']['name'];
    $fileType = pathinfo($uploadName, PATHINFO_EXTENSION);
    if (!in_array($fileType, $allowed)) {
        returnFail("File type not allowed");
    }

    //Get uploaded file contents
    $subContents = file_get_contents($_FILES['subtitleFile']['tmp_name']);


    //Load library for subtitle creation and conversion
    use \Done\Subtitles\Subtitles;

    //Load subtitles to force convert to srt
    $subtitlesConverted = Subtitles::loadFromString($subContents);
    $subContents = $subtitlesConverted->content('srt');

    //Create parser and load it with srt
    use Benlipp\SrtParser\Parser;
    $parser = new Parser();
    $parser->loadString($subContents);
    $captions = $parser->parse();

    //Get all texts into an array
    $captionsArrayToTranslate = [];
    foreach($captions as $caption) {
        array_push($captionsArrayToTranslate, $caption->text);
    }

    //Counter for billed characters
    $billedChars = 0;

    //Enclose in try to return an error if API Key is invalid or other error
    try {
            foreach($targets as $target) {
                //Translate subs
                $translationResult = $deepl->translateText($captionsArrayToTranslate, $source, $target, ['formality' => $formality]);

                //Add currently billed chars to global counter
                foreach($translationResult as $result) {
                    $billedChars += $result->billedCharacters;
                }

                $subtitlesTranslated = new Subtitles();
                
                for($i = 0; $i<count($captionsArrayToTranslate); $i++) {
                    //Recreate subtitles with original times but translated subtitles
                    $subtitlesTranslated->add($captions[$i]->startTime, $captions[$i]->endTime, $translationResult[$i]);
                }

                $subtitlesTranslatedString = $subtitlesTranslated->content('srt');

                $response["data"]["translations"][$target] = $subtitlesTranslatedString;
            }

            //Return status
            $response["status"] = "success";
            //Return billed chars
            $response["data"]["billedChars"] = $billedChars;

            echo json_encode($response);

            //Update usage for GUI index after each translation
            shell_exec("php get-usage.php &");
    }
    catch(Exception $e) {
        //Get error message and return it
        http_response_code(500);
        $response["status"] = "error";
        $response["message"] = $e->getMessage();

        echo json_encode($response);
    }


?>