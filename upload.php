<?php
require 'vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

$image = new claviska\SimpleImage();

if (isset($_POST['lines'])) {
  print_r(json_decode($_POST['lines']));
}

if (isset($_FILES['image'])) {
  $file = $_FILES['image'];
  $file_name = $_FILES['image']['name'];
  $file_tmp = $_FILES['image']['tmp_name'];
  $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
  move_uploaded_file($file_tmp, "image/" . $file_name);

  // ->desaturate()->invert()->desaturate()->invert()->brighten(20)
  $image->fromFile('image/' . $file_name)->desaturate()->brighten(50)->sharpen(95)->toFile('image/' . $file_name, 'image/png');
?>
  <a href="index.php">Back</a>
  <br>
<?php
  echo "version : " . (new TesseractOCR())->version() . "<br>";
  echo "<img height='400px' src='image/" . $file_name . "'></img> <br>";

  $result = (new TesseractOCR('image/' . $file_name))
    ->psm(3)
    ->run();

  // echo trim(preg_replace('/\s+/', ' ', $result)) . "<br>";
}
?>

<div id="result">
  <label for="file">Text Recognition progress:</label>
  <progress id="file" value="0" max="1"> 0% </progress>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src='./tesseract.min.js'></script>
<script>
  let image = "<?php echo 'image/' . $file_name; ?>";
  // import Tesseract from 'tesseract.js';

  Tesseract.recognize(
    image,
    'eng', {
      logger: m => {
        m.workerId ? document.getElementById("file").value = m.progress : 0
        // console.log(m)
      }
    }
  ).then(({
    data: {
      text,
      lines
    }
  }) => {
    document.getElementById("result").innerHTML = text;
    let data_text = JSON.stringify(lines);
    localStorage.setItem('lines', data_text);
    // $.ajax({
    //   url: "upload.php",
    //   method: "post",
    //   data: {
    //     lines: JSON.stringify(lines)
    //   },
    //   success: function(result) {
    //     // console.log(result);
    //   }
    // })
  })
</script>