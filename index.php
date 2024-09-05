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
        <div class="progress center-block"style="width:50%">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="<?=$characterLimit-$charactersUsed?>" aria-valuemin="0" aria-valuemax="<?=$characterLimit?>" style="width: <?=($characterLimit-$charactersUsed)/$characterLimit*100?>%"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>