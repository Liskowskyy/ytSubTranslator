<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subtitle Translator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .progress { margin-left: auto; margin-right:auto; }
    </style>
</head>
<body>
    <h1 class="text-center">Hello world!</h1>

    <?php
        //Get usage data from API
        $json = file_get_contents("http://{$_SERVER['HTTP_HOST']}/api/get-usage.php");
        $stats = json_decode($json);
        $stats = $stats->data;

        $charactersUsed = $stats->charactersUsed;
        $characterLimit = $stats->characterLimit;
    ?>

    <p class="text-center">As of now, youse have used <?=number_format($charactersUsed)?> of <?=number_format($characterLimit)?> characters for translation shared between all users.</p>
    
    <div class="text-center">
        <div class="progress center-block" style="width:50%">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="<?=$characterLimit-$charactersUsed?>" aria-valuemin="0" aria-valuemax="<?=$characterLimit?>" style="width: <?=($characterLimit-$charactersUsed)/$characterLimit*100?>%"></div>
        </div>
    </div>

    <?php
        //Get langs from API
        $json = file_get_contents("http://{$_SERVER['HTTP_HOST']}/api/list-langs.php");
        $langs = json_decode($json);
        $sourceLangs = $langs->data->sourceLanguages;
        $targetLangs = $langs->data->targetLanguages;
    ?>

    <br>

    <form action="" method="post" enctype="multipart/form-data" class="text-center">
        <div class="custom-file center-block">
            <input type="file" class="custom-file-input" id="subtitleFile" name="subtitleFile" accept=".srt, .vtt, .sbv" required>
            <label class="custom-file-label" for="subtitleFile">Choose file</label>
        </div>

        <br>

        <div class="row justify-content-center">
        <label class="form-select-label" for="sourceLangSelect">Source language:</label>
            <select class="form-control text-center center-block" id="sourceLangSelect" name="source" style="width:50%">
                <?php
                    //List all source langs
                    foreach ($sourceLangs as $sourceLang) {
                ?>
                    <option value="<?=$sourceLang->code?>"><?=$sourceLang->name?></option>
                <?php
                    }
                ?>
            </select>
        </div>

        <br>

        <div class="row justify-content-center">
            <label>Target languages:</label>
            <div class="center-block" style="width:50%">
                <?php
                    //List all target langs
                    foreach ($targetLangs as $targetLang) {
                ?>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" class="form-check-input target-lang-checkbox" value="<?=$targetLang->code?>">
                        <label class="form-check-label" for="<?=$targetLang->code?>"><?=$targetLang->name?></label>
                    </div>
                <?php
                    }
                ?>
            </div>
        </div>

        <br>

        <input id="targets" type="hidden" name="targets" value="">
        <button type="submit" class="btn btn-primary" id="submit">Translate</button>
    </form>

    <?php
        //Send request to API if it was sent to site
        if(isset($_FILES["subtitleFile"]) && isset($_POST["source"]) && isset($_POST["targets"])) {
            $url = "http://{$_SERVER['HTTP_HOST']}/api/translate.php";

            $uploadName = $_FILES['subtitleFile']['name'];
            $curlFile = curl_file_create($_FILES['subtitleFile']['tmp_name'], posted_filename: $uploadName);

            $postData = array(
                'subtitleFile' => $curlFile,
                'source' => $_POST["source"],
                'targets' => $_POST["targets"],
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($postData));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            $result = json_decode($result);

            $translations = $result->data->translations;

            curl_close($ch);
    ?>

    <?php
            //Download each translation as a file
            foreach($translations as $translationTarget => $translationContent) {
    ?>
                <iframe src="text_to_file.php?content=<?=urlencode($translationContent)?>&target=<?=$translationTarget?>"></iframe>
    <?php
            }
        }
    ?>


    <script
    src="https://code.jquery.com/jquery-3.7.1.slim.min.js"
    integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8="
    crossorigin="anonymous"></script>

    <script>
        //Generate comma seperated list of target languages into hidden targets value
        $('.target-lang-checkbox').click(function() {
        let targets = ($('.target-lang-checkbox:checked').map(function() {
            return this.value;
        }).get().join(','));
        $("#targets").val(targets);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>