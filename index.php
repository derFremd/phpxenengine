<html lang="en">
<header>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
</header>
<body>

<h3>Tests list</h3>
<ul>
<?php
        $listTest = [
            'ClassLoaderSample',
            'TemplateLoaderSample',
            'TemplateFileLoaderSample',
            'VisualBlockSample'
        ];

        foreach ($listTest as $item) {
            echo "\t<li><a href='samples/Samples?id=$item' target='_blank'>/samples/$item.php</a>\n";
        }
?>
</ul>
</body>
</html>