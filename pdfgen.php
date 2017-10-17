<?php

$books = scandir("könyvek/",0);

$books = array_diff($books, array(".", "..", "Extrák"));

foreach($books as $dir) {
	echo "Doing ".$dir."\n";
	if (is_file('/var/www/html/web/könyvek/'.$dir.'/PDF/'.$dir.'.pdf')) {
		echo "Skipped ".$dir."\n";
		continue;
	}
	//cwd
	chdir("/var/www/html/web/könyvek/".$dir."/JPEG");
	echo exec('for f in *.jpg; do convert "$f" "$f.pdf"; done');
	echo exec('pdftk *.pdf cat output "'.$dir.'.pdf"');
	echo exec('mv "'.$dir.'.pdf" "../PDF/'.$dir.'.pdf" ');
	echo exec("rm *.pdf");
	//exec("export MAGICK_TEMPORARY_PATH=/var/www");
 	//echo exec('convert "/var/www/html/web/könyvek/'.$dir.'/JPEG/*.jpg" "/var/www/html/web/könyvek/'.$dir.'/PDF/'.$dir.'.pdf" ');
}
?>
