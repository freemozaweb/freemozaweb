<?php
mb_internal_encoding("UTF-8");
//simple dom parser
require "html.php";

function combineArrays($full, $sub) {
	$array = array_merge($full, $sub);
	return $array;
}

function downloadBookList($full_list) {
	$ua = "Mozilla/5.0 (X11; OpenBSD i386) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36";
	//all subjects
	$url = "https://www.mozaweb.hu/course.php?cmd=book_list_inner&spec=subjects";

	$bigarray = array();



	//first get values such as maxpage and image locations
	$options  = array('http' => array('user_agent' => $ua));
	$context  = stream_context_create($options);
	
	$basic_array = [];

	$html = file_get_html($url);
	foreach($html->find('a[class=altema]') as $element) {
		$array[] = $element->href."\n";
	}

	foreach ($array as $key => $value) {
			parse_str($value, $result);
			$basic_array[str_replace("book_list_inner", "get_book_scroll_html", $value)] = [$result["subject"] => $result["serie"]];

	}

	if ($full_list) {
		foreach ($basic_array as $key => $value) {
			parse_str($key, $result);
			$subsubject = $result["serie"];
			$url = str_replace($subsubject, "", $key);
			$new_array[$url] = $value;	
		}
		$basic_array = $new_array;
	}
	//print_r($basic_array);
	//print_r($new_array);
	$books_array = [];
	$description_array = [];
	
	foreach ($basic_array as $key => $value) {
		//print ("Current: ".$key);
		$url = trim("https://www.mozaweb.hu".str_replace("list","block",$key)."&from=");
		$url = trim(preg_replace('/\s+/', '', $url));

		parse_str($url, $result);		

		$subject = $result["subject"];
		$subsubject = $result["serie"];
		
		//download first with this commented out
		//then with it in
		//then array merge like in test.php
		if ($full_list) {
			$subsubject = $subject;
		}

		print $url."\n";

		$full = '';
		$position = 0;
		$offset = 1;

		while (true) {
			$str = file_get_contents($url.$position, false, $context);
			if ($str == '{"html":null,"finished":true}') {
				break;
			}

			$str = str_replace("\\n", " ", $str);
			$str = str_replace(array('{"html":"', '","finished":false}', '\\', 'amp;', ""), '', $str);

			$full = $full."<span>Seperate Span</span>".$str;

			$position = $position + $offset;
			print $position."\n";
		}

		//create full html
		$str = $full;
		//print ($str);
	
		$html = str_get_html($str);
		foreach($html->find('a[class=title]') as $element) {

			   $string = $element->href;
			   parse_str($string, $result);
			   $number = $result["bid"];
			   
			   //little something to stare at	
			   //print $string;

		   	   $books_array[$element->plaintext] = $number.":".$subject.":".$subsubject;
		}
		//make unique
		$books_array = array_unique($books_array);
		//return $books_array;
	}


	return $books_array;
}
 
//Code to download book list(s)
/*
$array_with_subsubjects = downloadBookList(false);
file_put_contents("books.txt", serialize($array_with_subsubjects));
$array_with_all = downloadBookList(true);
file_put_contents("books_all.txt", serialize($array_with_all));
die(0);
*/

//code to combine book lists
/*
$full = unserialize(file_get_contents("books_all.txt"));
$sub =  unserialize(file_get_contents("books.txt"));
$final = combineArrays($full, $sub);
file_put_contents("final.txt", serialize($final));
*/

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function downloadBook($name, $code, $subject, $subsubject) {
	$code = trim($code);
	$ua = "Mozilla/5.0 (X11; OpenBSD i386) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36";
	$url = "https://www.mozaweb.hu/mblite.php?cmd=open&bid=".$code."&page=1";
	$base_url = "https://www.mozaweb.hu/course.php?cmd=single_book&bid=".$code;
	
	//create folders and files
	$foldername = $code . " - " . $name;

	if (is_dir($foldername)) {
		echo "Already exists!\n";
		return 1;
	}

	mkdir($foldername);
	mkdir($foldername."/JPEG");
	mkdir($foldername."/PDF");
		
	//get description, authors, and price
	$html = file_get_html($base_url);
	$tags = get_meta_tags($base_url);

	foreach ($html->find('p[class=mb0]') as $key => $value) {
		$authors = trim(str_replace("Szerzők: ", "", $value->plaintext));
		break;
	}

	//price js-price-mozaweb-bold
	foreach ($html->find('span[class=price js-price-mozaweb-bold]') as $key => $value) {
		$price = trim(str_replace("Ft","",$value->plaintext));
	}

	foreach ($html->find('div[class=description]') as $key => $value) {
		foreach ($value->find('p') as $key_1 => $value_1) {
			if (strpos($value_1->plaintext, $code)) {
				$description = trim($value_1->plaintext);
				break;
			}
		}
	}

	if (!isset($price)) {
		$price = "Ismeretlen";
	}

	if ($subject == $subsubject) {
                        $subsubject = "Ismeretlen";
        }


	$description = preg_replace('/\s\s+/', ' ', $description);
	$additonal_description = $tags["description"];
	
	//first get values such as maxpage and image locations
	$options  = array('http' => array('user_agent' => $ua));
	$context  = stream_context_create($options);

	$html = file_get_contents($url, false, $context);
	
	
        if (!$html) {
            shell_exec("rm -r '".$foldername."'");
            mkdir($foldername);
        	      mkdir($foldername."/JPEG");
        mkdir($foldername."/PDF");
	    file_put_contents($foldername."/bocs.txt", "Ez a tankönyv nem elérhető a Mozaweben!");
            return 0;
        }


	$maxpage = get_string_between($html, '"maxpage":"', '",');

	if (!$maxpage) {
		shell_exec("rm -r '".$foldername."'");
        mkdir($foldername);
	mkdir($foldername."/JPEG");
        mkdir($foldername."/PDF");
        file_put_contents($foldername."/bocs.txt", "Ez a tankönyv nem elérhető a Mozaweben!");
	//$maxpage = "Ismeretlen";
        return 0;

	}

	$info_file = "Név: ".$name."\nKód: ".$code."\nKiadás:\n".$description."\nLeírás:\n".$additonal_description."\nTantárgy: ".$subject."\nTéma: ".$subsubject."\nSzerző(k): \n".$authors."\nÁr HUF: ".$price."\nLapszám: ".$maxpage."\n\nADOMÁNYOZZ (BITCOIN): 1HkJGiwphqQEYFEhQwPCogaphAUoS7qyrq";

	$machine_info_file = base64_encode($name).":".base64_encode($code).":".base64_encode($description).":".base64_encode($additonal_description).":".base64_encode($subject).":".base64_encode($subsubject).":".base64_encode($authors).":".base64_encode($price).":".base64_encode($maxpage);

	file_put_contents($foldername."/info.txt", $info_file);
	file_put_contents($foldername."/machine_info.txt", $machine_info_file);
	
	//get first image location
	$location =  "https://www.mozaweb.hu/".str_replace('\\', '', get_string_between($html, '"dirWeb":"', '",').$code."_");
	
	//create range array
	$range = range("001", $maxpage);
	
	//now fix page numbers (ex 11 => 011)
	foreach ($range as $key => $value) {
		if ($value < 10) {
			$range[$key] = ("00".$value);
		} elseif ($value < 100 and $value > 9) {
			$range[$key] = ("0".$value);
		}
	}

	//download cover
	$cover = get_string_between($html, '<meta property="og:image" content="cover/', '" />');
	$cover = "https://www.mozaweb.hu/cover/".$cover;

	file_put_contents($foldername."/cover.jpg", file_get_contents($cover, false, $context));

	//now download pages
	foreach ($range as $key => $value) {
		$data = file_get_contents($location.$value.".jpg", false, $context);
		if (!$data) {
			echo "Error while downloading: ".$data." file: ".$location.$value.".jpg";
			sleep(5);
			$data = file_get_contents($location.$value.".jpg", false, $context);
		}
		file_put_contents($foldername."/JPEG/".$code."_".$value.".jpg", $data);
		echo "Downloaded ".$location.$value.".jpg!\n";
	}
}

function loadData($file) {
		$data = file_get_contents($file);
		$data = unserialize($data);
		return $data;
}

//fix shit
function addInfoFilesAgain($name, $code, $subject, $subsubject) {
		$code = trim($code);
		$ua = "Mozilla/5.0 (X11; OpenBSD i386) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36";
		$url = "https://www.mozaweb.hu/mblite.php?cmd=open&bid=".$code."&page=1";
		$base_url = "https://www.mozaweb.hu/course.php?cmd=single_book&bid=".$code;
		
		//create folders and files
		$foldername = $code . " - " . $name;

		if (!is_dir($foldername)) {
			echo "NOT EXISTS";
			return 1;
		}

		//get description, authors, and price
		$html = file_get_html($base_url);
		$tags = get_meta_tags($base_url);

		foreach ($html->find('p[class=mb0]') as $key => $value) {
			$authors = trim(str_replace("Szerzők: ", "", $value->plaintext));
			break;
		}

		//price js-price-mozaweb-bold
		foreach ($html->find('span[class=price js-price-mozaweb-bold]') as $key => $value) {
			$price = trim(str_replace("Ft","",$value->plaintext));
		}

		if (!isset($price)) {
			$price = "Ismeretlen";
		}

		if ($subject == $subsubject) {
			$subsubject = "Ismeretlen";
		}

		foreach ($html->find('div[class=description]') as $key => $value) {
			foreach ($value->find('p') as $key_1 => $value_1) {
				if (strpos($value_1->plaintext, $code)) {
					$description = trim($value_1->plaintext);
					break;
				}
			}
		}
		$description = preg_replace('/\s\s+/', ' ', $description);
		$additonal_description = $tags["description"];
		
		//first get values such as maxpage and image locations
		$options  = array('http' => array('user_agent' => $ua));
		$context  = stream_context_create($options);

		$html = file_get_contents($url, false, $context);

		if (!$html) {
			shell_exec("rm -r '".$foldername."'");
			mkdir($foldername);
			file_put_contents($foldername."/bocs.txt", "Ez a tankönyv nem elérhető a Mozaweben!");
			return 0;
		}

		$maxpage = get_string_between($html, '"maxpage":"', '",');

		if (!$maxpage) {
			shell_exec("rm -r '".$foldername."'");
        	mkdir($foldername);
        	file_put_contents($foldername."/bocs.txt", "Ez a tankönyv nem elérhető a Mozaweben!");
        	return 0;
		}

		$info_file = "Név: ".$name."\nKód: ".$code."\nKiadás:\n".$description."\nLeírás:\n".$additonal_description."\nTantárgy: ".$subject."\nTéma: ".$subsubject."\nSzerző(k): \n".$authors."\nÁr HUF: ".$price."\nLapszám: ".$maxpage."\n\nADOMÁNYOZZ (BITCOIN): 1HkJGiwphqQEYFEhQwPCogaphAUoS7qyrq";

		$machine_info_file = base64_encode($name).":".base64_encode($code).":".base64_encode($description).":".base64_encode($additonal_description).":".base64_encode($subject).":".base64_encode($subsubject).":".base64_encode($authors).":".base64_encode($price).":".base64_encode($maxpage);

		file_put_contents($foldername."/info.txt", $info_file);
		file_put_contents($foldername."/machine_info.txt", $machine_info_file);

}

//code to actually download books
$bigarray = loadData("final.txt");

foreach ($bigarray as $key => $value) {
	$book_name = $key;
	$exploded = explode(":",  $value);
	$code = $exploded[0];
	$subject = $exploded[1];
	$subsubject = $exploded[2];
	
	//conditional download , remove to dl all
	if ($code == "MS-1731" or $code == "MS-1731-EN" or $code == "MS-2619U") {
	  print "Downloading ".$book_name." code ".$code." subject ".$subject." subsubject ".$subsubject."\n";
        downloadBook($book_name, $code, $subject, $subsubject);

	}
//	print "Downloading ".$book_name." code ".$code." subject ".$subject." subsubject ".$subsubject."\n";
    //    downloadBook($book_name, $code, $subject, $subsubject);
//	print "Downloading ".$book_name." code ".$code." subject ".$subject." subsubject ".$subsubject."\n";
//	downloadBook($book_name, $code, $subject, $subsubject);
	//print "Fixing ".$book_name." code ".$code." subject ".$subject." subsubject ".$subsubject."\n";
	//addInfoFilesAgain($book_name, $code, $subject, $subsubject);	

}


?>

