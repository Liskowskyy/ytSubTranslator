<?php
    $content = $_GET['content'];
    $filename = date(DATE_ATOM)."-".$_GET['target'];
    header("Content-Type: plain/text");
    header("Content-Disposition: Attachment; filename=".$filename.".srt");
    header("Pragma: no-cache");
    echo "$content";