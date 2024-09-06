<?php
    require("api/common.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subtitles downloading...</title>
    <meta http-equiv="refresh" content="0;url=<?=$protocol.$_SERVER['HTTP_HOST']?>" />
</head>
<body>
    <?php
            //Send request to API if it was sent to site
            if(isset($_FILES["subtitleFile"]) && isset($_POST["source"]) && isset($_POST["targets"])) {
                $url = "{$protocol}{$_SERVER['HTTP_HOST']}/api/translate.php";

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

                //Display message on error
                if($result->status != "success") {
                    echo "<p class='text-center'>Failed: ".$result->message.'</p>';
                    exit();
                }

                $translations = $result->data->translations;

                curl_close($ch);
        ?>

        <?php
                //If one file then download directly
                if(count((array)$translations) == 1) {
                    foreach($translations as $translationTarget => $translationContent) {
                ?>
                    <iframe src="text_to_file.php?content=<?=urlencode($translationContent)?>&target=<?=$translationTarget?>"></iframe>
                <?php
                    }
                }
                else if(count((array)$translations) > 1) {
                    //Pack each file into one zip
                    $zip = new ZipArchive;
                    $file = tempnam('.', 'zip');
                    register_shutdown_function('unlink', $file);
                    $zip->open($file, ZipArchive::OVERWRITE);

                    foreach($translations as $translationTarget => $translationContent) {
                        $zip->addFromString($translationTarget.".srt", $translationContent);
                    }

                    $zip->close();
                    $filename = date(DATE_ATOM).".zip";
                    header('Content-Type: application/zip');
                    header('Content-Length: ' . filesize($file));
                    header('Content-Disposition: attachment; filename="'.$filename.'"');
                    readfile($file);
                }
            }
        ?>
</body>
</html>