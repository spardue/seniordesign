<?php
include_once("config.php");

function addToTemplates($pdffile)
{
    $execCmd = "pdftk " . $pdffile . " generate_fdf output " . FDF_DIR . "/" . substr($pdffile, 0, -4) . ".fdf";
    exec($execCmd);
    rename($pdffile, PDF_DIR . "/" . $pdffile);
}

if (count($argv) == 2) {
    addToTemplates($argv[1]);
}
?>
