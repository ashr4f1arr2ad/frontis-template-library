<?php
echo "POST max: " . ini_get('post_max_size') . "\n";
echo "Upload max: " . ini_get('upload_max_filesize') . "\n";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST received! Size: " . $_SERVER['CONTENT_LENGTH'] . " bytes\n";
    var_dump($_POST);
    var_dump($_FILES);
} else {
    echo '<form method="post" enctype="multipart/form-data">
            <input type="file" name="testfile">
            <button type="submit">Send ~15MB file</button>
          </form>';
}