<?php
$lines = "<script>
  let lines = localStorage.getItem('lines');
  document.writeln(lines);
</script>";

echo $lines;