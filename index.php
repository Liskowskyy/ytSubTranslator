<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subtitle Translator</title>
</head>
<body>
    <h1>Hello world!</h1>

    <?php
        //Get usage data from API
        $json = file_get_contents("http://{$_SERVER['HTTP_HOST']}/api/get-usage.php");
        $stats = json_decode($json);
        $stats = $stats->data;

        $charactersUsed = $stats->charactersUsed;
        $characterLimit = $stats->characterLimit;
    ?>

    <p>As of now, youse have used <?=number_format($charactersUsed)?> of <?=number_format($characterLimit)?> characters for translation shared between all users.</p>

</body>
</html>