<?php
        require("api/common.php");
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved translations | Bulk Subtitle Translator</title>
    <meta content="Saved translations" property="og:title">
    <meta content="View your previous translations" property="og:description">
    <meta content="Bulk Subtitle Translator" property="og:site_name">
    <meta content="<?=$protocol.$fullURL?>/android-chrome-512x512.png" property='og:image'>
    <meta name="theme-color" content="#373F47">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .navbar {
            padding: 0 !important;
        }
        .card {
            margin-top: 5%;
            margin-bottom: 5%;
        }
        .card-text {
            font-size: small;
            font-style: italic;
        }
        body {
            padding-bottom: 65px;
        }
    </style>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
</head>
<body style="margin-left: 5%; margin-right: 5%;">
    <h1 class="text-center col-lg-6 offset-lg-3">Saved translations</h1>

    <div id="savedList" class="col-lg-4 offset-lg-4">
        <div class="card" id="cardToClone" style="display: none;">
            <div class="card-header">
                Translation
            </div>
            <div class="card-body">
                <h5 class="card-title">Time and date</h5>
                <p class="card-text">Small excerpt from translation</p>
                <a href="#" class="btn btn-primary">Download</a>
            </div>
        </div>
    </div>

    <script
    src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
    crossorigin="anonymous"></script>

    <?php
        require("navbar.html");
    ?>

    <script type="text/javascript" src="jszip.min.js"></script>
    <script type="text/javascript" src="FileSaver.min.js"></script>
    <script>
        function loadItems() {
            $( ".clonedCard" ).remove();

            const items = {...localStorage};
            let orderedItemsValues = [];
            $.each(items, function(key, value) {
                orderedItemsValues.push($.parseJSON(value)); //Add to new array
            });
            orderedItemsValues.sort(function(a, b){
                return b.timestamp - a.timestamp; //Order by newest
            });

            let cardToClone = $('#cardToClone').clone();
            cardToClone.removeAttr("id");
            cardToClone.addClass("clonedCard")
            cardToClone.css("display", "");
            $.each(orderedItemsValues, function(key, value) {
                let dateString = new Date(value.timestamp).toLocaleString();

                let newCard = cardToClone.clone();
                newCard.find(".card-title").html(dateString);
                newCard.find(".card-text").html(value.origFile.replace(/(\r\n|\r|\n)/g, '<br>'));
                newCard.find(".btn-primary").attr("translations", JSON.stringify(value.translations));
                newCard.find(".btn-primary").attr("filename", value.filename);
                newCard.find(".btn-primary").click(function() {
                    let translations = $(this).attr("translations");
                    translations = JSON.parse(translations);
                    let translationsCodes = Object.keys(translations);
                    let filename = $(this).attr("filename");

                    if(translationsCodes.length == 1) {
                        //Save into a single .srt file if one translation
                        let langCode = translationsCodes[0];
                        let content = translations[langCode];

                        let blob = new Blob([content], {type: "text/plain;charset=utf-8"});
                        saveAs(blob, filename);
                    }
                    else if(translationsCodes.length > 1) {
                        //Save into a .zip archive if multiple translations
                        let zip = new JSZip();

                        for (const [langCode, content] of Object.entries(translations)) {
                            zip.file(langCode+".srt", content);
                        }

                        zip.generateAsync({type:"blob"})
                            .then(function(blob) {
                                saveAs(blob, filename);
                        });
                    }
                })
                newCard.appendTo("#savedList");
            });
        }

        $( document ).ready(loadItems); //Load items on page load
        window.addEventListener("storage", loadItems, false); //Reload items on any change
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>