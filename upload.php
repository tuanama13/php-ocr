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

    $image->fromFile('image/' . $file_name)->desaturate()->brighten(40)->sharpen(80)->toFile('image/' . $file_name, 'image/png');
  ?>
    <a href="index.php">Back</a>
    <br>
  <?php
    echo "version : " . (new TesseractOCR())->version() . "<br>";
    echo "<img height='400px' src='image/" . $file_name . "'></img> <br>";

    $result = (new TesseractOCR('image/' . $file_name))
      ->psm(3)
      ->run();
  }
  ?>

  <div id="result">
    <label for="file">Text Recognition progress:</label>
    <progress id="file" value="0" max="1"> 0% </progress>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  <script src='./tesseract.min.js'></script>
  <script src='./caman.full.min.js'></script>
  <script>
    let image = "<?php echo 'image/' . $file_name; ?>";

    const { createWorker } = Tesseract;

    const worker = createWorker(
      {
        langPath: "./traineddata",
        gzip: false, 
        logger: m => {
            m.workerId ? document.getElementById("file").value = m.progress : 0
          },
      }
    );

    (async () => {
      await worker.load();
      await worker.loadLanguage('eng');
      await worker.initialize('eng');
      await worker.setParameters({
        tessedit_char_whitelist: '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRSTUVYZXW-/: .,',
      });
      const { data: { text,lines } } = await worker.recognize(image);
      document.getElementById("result").innerHTML = extractText(lines);
      console.log(lines);
      await worker.terminate();
    })();

    const word_list = [
      "WK"
    ]


    function extractText(value) {
      const data = {
        'Provinsi' : false,
        'Kabupaten' : false,
        'NIK' : false,
        'Nama' : false,
        'Gender' : false,
        'TTL': false,
        'Alamat': false,
        'Agama': false,
      }

      for (const i of value) {
        if (i.text.includes("PROVINSI")) {
          data.Provinsi = i.text.split("PROVINSI");
          data.Provinsi = data.Provinsi[1].toString().replace("\n", "");
          data.Provinsi = clearText(data.Provinsi.trim());
          data.Provinsi = true;
        }

        if (i.text.includes("KABUPATEN")) {
          data.Kabupaten = i.text.split("KABUPATEN");
          data.Kabupaten = data.Kabupaten[1].toString().replace("\n", "");
          data.Kabupaten = clearText(data.Kabupaten.trim());
          data.Kabupaten = true;

        }
        if (i.text.includes("KOTA")) {
          data.Kabupaten = i.text.split("KOTA");
          data.Kabupaten = data.Kabupaten[1].toString().replace("\n", "");
          data.Kabupaten = clearText(data.Kabupaten.trim());
          data.Kabupaten = true;
        }

        if (i.text.includes("NIK") || i.text.includes("WK")) {
          data.NIK = i.text.toString().replace(/NIK|WK|KNIK/g, "");
          data.NIK = wordToNumber(data.NIK.toString().replace(" ", ""));
          data.NIK = clearText(data.NIK.trim());
          data.NIK = true;
        }

        if (i.text.includes("Nama")) {
          data.Nama = i.text.split("Nama");
          data.Nama = data.Nama[1].toString().replace("\n", "");
          data.Nama = clearText(data.Nama.trim());
          data.Nama = true;
        }

        if (i.text.includes("Kelamin")) {
          data.Gender = i.text.split("Kelamin");
          data.Gender = data.Gender[1].toString().replace("\n", "");
          data.Gender = clearText(data.Gender.trim());
          data.Gender = true;
        }

        if (i.text.includes("Lahir")) {
          data.TTL = i.text.split("Lahir");
          // data.TTL = data.TTL[1].toString().replace("\n", "");
          data.TTL = data.TTL.toString().replace(":", "");
          data.TTL = clearText(data.TTL.trim());
          data.TTL = true;
        }

        if (i.text.includes("Alamat")) {
          data.Alamat = i.text.split("Alamat");
          data.Alamat = data.Alamat[1].toString().replace("\n", "");
          data.Alamat = clearText(data.Alamat.trim());
          data.Alamat = true;
        }

        if (i.text.includes("Agama")) {
          data.Agama = i.text.split("Agama");
          data.Agama = data.Agama[1].toString().replace("\n", "");
          data.Agama = clearText(data.Agama.trim());
          data.Agama = true;
        }
      }

      return JSON.stringify(data);
    }

    function clearText(value){
      let str = value.replace( /[a-z]/g, '' );
      return str;
    }

    function wordToNumber(value) {
      const word_dict = {
          "L" : "1",
          'l' : "1",
          'O' : "0",
          'o' : "0",
          'b' : "6",
          'k' : "6",
          'E' : "6",
          'Y' : "4",
          '?' : "7",
          ' ' : '',
          '-' : '',
          ',' : '',
          'e' : '',
          '\n' : '',
          ':' : '',
          '.' : ''
      }
      let res = ''
      for (const letter of value) {
        if (word_dict.hasOwnProperty(letter)) {
          res += word_dict[letter]
        }else{
          res += letter;
        }
      }
      return res;
    }
  </script>