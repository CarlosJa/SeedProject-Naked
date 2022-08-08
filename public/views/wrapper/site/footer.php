
<?php
if($this->JavaScript) {
    foreach ($this->JavaScript as $kj => $JavaScript) :
        if(!is_numeric($kj)) {
            echo PHP_EOL;
            echo $JavaScript . PHP_EOL;
            continue;
        }
        echo '<script src="'. $JavaScript .'"></script>' . PHP_EOL;
    endforeach;
}
?>


</body>
</html>