<?php
    require("api/common.php");
?> 
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
                    'formality' => $_POST["formality"],
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
            ob_clean();
            ob_end_flush(); //ZIP Downloading is broken without it
                //If one file then download directly
                if(count((array)$translations) == 1) {
                    foreach($translations as $translationTarget => $translationContent) {
                        $filename = date(DATE_ATOM)."-".$translationTarget.".srt";
                        header('Content-Type: application/zip');
                        header('Content-Length: ' . strlen($translationContent));
                        header('Content-Disposition: attachment; filename="'.$filename.'"');
                        echo $translationContent;
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
                    //header('Content-Length: ' . filesize($file)); //Breaks downloads on server!
                    header('Content-Disposition: attachment; filename="'.$filename.'"');
                    readfile($file);
                }
            }

            //Don't put HTML below, it's just such a headache
        ?>