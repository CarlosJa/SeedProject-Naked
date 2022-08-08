<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="CarlosArias.com">
    <meta name="generator" content="">
    <title>SeedProject - Framework Installation </title>
    <link href="/install/assets/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
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

<header>
    <div class="collapse bg-dark" id="navbarHeader">
        <div class="container">
            <div class="row">
                <div class="col-sm-8 col-md-7 py-4">
                    <h4 class="text-white">About</h4>
                    <p class="text-muted">Add some information about the album below, the author, or any other background context. Make it a few sentences long so folks can pick up some informative tidbits. Then, link them off to some social networking sites or contact information.</p>
                </div>
                <div class="col-sm-4 offset-md-1 py-4">
                    <h4 class="text-white">Contact</h4>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Follow on Twitter</a></li>
                        <li><a href="#" class="text-white">Like on Facebook</a></li>
                        <li><a href="#" class="text-white">Email me</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a href="#" class="navbar-brand d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" aria-hidden="true" class="me-2" viewBox="0 0 24 24"><path class="blueprint_een" d="M30,1H2C1.448,1,1,1.448,1,2v28c0,0.552,0.448,1,1,1h28c0.552,0,1-0.448,1-1V2
	C31,1.448,30.552,1,30,1z M29,29H3V3h26V29z M5,8H4V4h4v1H5V8z M28,28h-4v-1h3v-3h1V28z M27,5h-3V4h4v4h-1V5z M8,28H4v-4h1v3h3V28z"/></svg>
                <strong>SeedProject</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHeader" aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </div>
</header>


