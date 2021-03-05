<?php
  require 'vendor/autoload.php';
  use thiagoalessio\TesseractOCR\TesseractOCR;

  $image = new claviska\SimpleImage();

  if (isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $file_name = $_FILES['image']['name'];
    $file_tmp = $_FILES['image']['tmp_name'];
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    move_uploaded_file($file_tmp, "image/" . $file_name);

    // ->desaturate()->invert()->desaturate()->invert()->brighten(20)
    $image->fromFile('image/' . $file_name)->desaturate()->brighten(45)->sharpen(80)->toFile('image/'.$file_name, 'image/png');
    ?>
    <a href="index.php">Back</a>
    <br>
    <?php
    // print_r($objFoo);
    echo "Image Upload Success <br>";
    echo "<img height='400px' src='image/".$file_name. "'></img> <br>";
    echo "version : ".(new TesseractOCR())->version()."<br>";

    echo (new TesseractOCR('image/'.$file_name))
    ->oem(5)
    ->run();
  }