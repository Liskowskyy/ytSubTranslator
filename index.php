<?php
        require("api/common.php");
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Subtitle Translator</title>
    <meta content="Bulk Subtitle Translator" property="og:title">
    <meta content="Translate your video's subtitles into 29 languages at once!" property="og:description">
    <meta content="Bulk Subtitle Translator" property="og:site_name">
    <meta content="<?=$protocol.$_SERVER['HTTP_HOST']?>/android-chrome-512x512.png" property='og:image'>
    <meta name="theme-color" content="#373F47">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .progress { margin-left: auto; margin-right:auto; }
        .fSupport {visibility: hidden;} /* Hide formality support by default */
        ul {
            column-count: 2;
            list-style: none;
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
    <h1 class="text-center col-lg-6 offset-lg-3">Hello world!</h1>
    <?php
        //Get usage data from API
        $json = file_get_contents("{$protocol}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}/api/get-usage.php.cache");
        $stats = json_decode($json);
        $stats = $stats->data;

        $charactersUsed = $stats->charactersUsed;
        $characterLimit = $stats->characterLimit;
    ?>

    <p class="text-center col-lg-6 offset-lg-3">As of now, youse have used <span id="tokenDisplay"><?=number_format($charactersUsed)?> of <?=number_format($characterLimit)?></span> characters for translation shared between all users.</p>
    
    <div class="col-lg-6 offset-lg-3">
        <div class="progress center-block">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" id="tokenBar" title="Percentage of characters left for translations" aria-valuenow="<?=$characterLimit-$charactersUsed?>" aria-valuemin="0" aria-valuemax="<?=$characterLimit?>" style="width: <?=($characterLimit-$charactersUsed)/$characterLimit*100?>%"></div>
        </div>
    </div>

    <?php
        //Get langs from API
        $json = file_get_contents("{$protocol}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}/api/list-langs.php");
        $langs = json_decode($json);
        $sourceLangs = $langs->data->sourceLanguages;
        $targetLangs = $langs->data->targetLanguages;
    ?>

    <br>

    <form action="api/translate.php" method="post" enctype="multipart/form-data" id="transForm" class="col-lg-4 offset-lg-4">
        <div class="form-group">
            <input type="file" class="form-control" type="file" id="subtitleFile" name="subtitleFile" accept=".srt, .vtt, .sbv" required>
        </div>

        <br>

        <div class="form-group">
            <div class="form-floating">
                <select class="form-control form-select" id="sourceLangSelect" name="source">
                    <?php
                        //List all source langs
                        foreach ($sourceLangs as $sourceLang) {
                    ?>
                        <option value="<?=$sourceLang->code?>"><?=$sourceLang->name?></option>
                    <?php
                        }
                    ?>
                </select>
                <label class="form-select-label" for="sourceLangSelect">Source language:</label>
            </div>
        </div>

        <br>

        <div class="form-group">
            <label>Target languages:</label>
            <ul>
                <?php
                    //List all target langs
                    foreach ($targetLangs as $targetLang) {
                ?>
                    <li>
                        <label>
                        <input type="checkbox" class="form-check-input target-lang-checkbox" id="<?=$targetLang->code?>" value="<?=$targetLang->code?>">
                        <span>
                            <?=$targetLang->name?>
                            <sup class="fSupport"> <!--Add a superscript f if target lang supports formality settings -->
                                <?php
                                    if($targetLang->supportsFormality) echo "f";
                                ?>
                        </span>
                        </label>
                    </li>
                <?php
                    }
                ?>
            </ul>
        </div>

        <span class="fSupport" style="font-size: small;">
            <sup>f</sup> - target language with support for formality settings
        </span>

        <br>

        <div class="form-group">
            <div class="form-floating">
                    <select class="form-control form-select" id="formalitySelect" name="formality">
                        <option value="informal">Informal</option>
                        <option selected="selected" value="default">Default</option>
                        <option value="formal">Formal</option>
                    </select>
                    <label class="form-select-label" for="formalitySelect">Formality:</label>
                </div>
        </div>

        <br>

        <input id="targets" type="hidden" name="targets" value="">

        <div class="form-group">
            <div class="col text-center">
                <button type="submit" class="btn btn-primary col-lg-6" id="submit">Translate</button>
            </div>
        </div>
    </form>


    <script
    src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
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

    <script>
        $("#formalitySelect").on("change", function() {
            if(this.value != "default") $('.fSupport').css('visibility', 'visible');
            else $('.fSupport').css('visibility', 'hidden')
        });
    </script>

    <script>
        function updateCharacterCount() {
            $.getJSON( "api/get-usage.php", function( data ) {
                let charactersUsed = data.data.charactersUsed;
                let formattedCharactersUsed = Number(charactersUsed).toLocaleString("en-US");
                let characterLimit = data.data.characterLimit;
                let formattedcharacterLimit = Number(characterLimit).toLocaleString("en-US");

                let width = `${(characterLimit-charactersUsed)/characterLimit*100}%`;

                $("#tokenDisplay").html(`${formattedCharactersUsed} of ${formattedcharacterLimit}`);

                $("#tokenBar").attr("aria-valuenow", characterLimit-charactersUsed);
                $("#tokenBar").attr("aria-valuemax", characterLimit);
                $("#tokenBar").css("width", width);
            });
        }

        let updateBar = window.setInterval(function(){
            updateCharacterCount()
        }, 10000);
    </script>

    <script>
        document.addEventListener('dragover', (e) => {
            e.preventDefault()
        });

        document.addEventListener('drop', (e) => {
            document.getElementById('subtitleFile').files = e.dataTransfer.files;
            e.preventDefault()
        });
    </script>

    <script type="text/javascript" src="jszip.min.js"></script>
    <script type="text/javascript" src="FileSaver.min.js"></script>
    <script>
        //Form POST handler
        $("#transForm").submit(function(e) {
            e.preventDefault();

            //Change submit text to spinner while request is being processed
            $('#submit').prop('disabled', true);
            let initialBtnText = $('#submit').html();
            $('#submit').html('<div class="spinner-border spinner-border-sm" role="status"></div>');

            let transForm = $(this);
            let URL = transForm.attr('action');

            $.ajax({
                type: "POST",
                url: URL,
                data: new FormData(this),
                processData: false, 
                contentType: false,
                success: function(data) {
                    let translations = data.data.translations;
                    let translationsCodes = Object.keys(translations)

                    if(translationsCodes.length == 1) {
                        //Save into a single .srt file if one translation
                        let langCode = translationsCodes[0];
                        let content = translations[langCode];
                        console.log(langCode, content);

                        let blob = new Blob([content], {type: "text/plain;charset=utf-8"});
                        let filename = new Date().toLocaleString()+"  "+langCode;
                        filename = filename.replace(/[^a-z0-9]/gi, '-')+".srt";
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
                                let filename = new Date().toLocaleString();
                                filename = filename.replace(/[^a-z0-9]/gi, '-')+".zip";
                                saveAs(blob, filename);
                        });
                    }
                    
                    //Restore submit text on success
                    $('#submit').prop('disabled', false);
                    $('#submit').html(initialBtnText);
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>