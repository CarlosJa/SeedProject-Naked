<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>SeedProject - Starter Framework </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="SeedProject Starter Framework" name="description" />
    <meta content="Carlos Arias" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Bootstrap Css -->
    <link href="/public/assets/css/bootstrap.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="/public/assets/css/custom.css" rel="stylesheet" type="text/css" />

    <?php
    if($this->Styles) {
        foreach ($this->Styles as $ks => $Styles) :
            if(!is_numeric($ks)) {
                echo PHP_EOL;
                echo $Styles . PHP_EOL;
                continue;
            }
            echo '<link type="text/css" rel="stylesheet" href="'. $Styles .'">' .PHP_EOL;

        endforeach;
    }
    ?>
</head>

<body>
