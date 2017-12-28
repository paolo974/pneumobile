<?php

error_reporting(E_ERROR | E_PARSE);

//$url = "https://www.norauto.fr/0/0/0/ete/0/195-65-15.html";

$s_saison = "ete";
$s_larg = "195";
$s_haut = "60";
$s_diam = "16";
$num_page = "1";

function getContentUrl($path, $url) {
 // http://coursesweb.net/php-mysql/
  // Seting options for cURL
  
  $fp = fopen($path, 'wb');
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_FILE, $fp);
  //curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
  //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/21.0 (compatible; MSIE 8.01; Windows NT 5.0)');
  //curl_setopt($ch, CURLOPT_TIMEOUT, 200);
  //curl_setopt($ch, CURLOPT_AUTOREFERER, false);
  //curl_setopt($ch, CURLOPT_REFERER, 'http://google.com');
  //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);    // Follows redirect responses

  // gets the file content, trigger error if false
  //$file = curl_exec($ch);
  curl_exec($ch);
  //if($file === false) trigger_error(curl_error($ch));

  curl_close ($ch);
  fclose($fp);
  //return $file;
}

function getContentUrl2($url) {
 // http://coursesweb.net/php-mysql/
  // Seting options for cURL
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/21.0 (compatible; MSIE 8.01; Windows NT 5.0)');
  curl_setopt($ch, CURLOPT_TIMEOUT, 200);
  curl_setopt($ch, CURLOPT_AUTOREFERER, false);
  curl_setopt($ch, CURLOPT_REFERER, 'http://google.com');
  curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);    // Follows redirect responses

  // gets the file content, trigger error if false
  $file = curl_exec($ch);
  if($file === false) trigger_error(curl_error($ch));

  curl_close ($ch);
  return $file;
}

$s_saison = "ete";
$s_larg = "195";
$s_haut = "60";
$s_diam = "16";
$num_page = "1";

function search($s_saison, $s_larg, $s_haut, $s_diam, $num_page) {

	$filename = "$s_saison$s_larg$s_haut$s_diam.data";

	$url = "https://www.norauto.fr/0/0/0/$s_saison/0/$s_larg-$s_haut-$s_diam.html?PageNumber=$num_page";
	$file_content = file_get_contents($url);
	//file_put_contents("data.data", $file_content);
	//$file_content = file_get_contents("data.data");
	
	$doc = new DOMDocument();
	$doc->loadHTML($file_content);
	$xpath = new DOMXpath($doc);
	
	$r = array();
	$res = "";
	
	$data = $xpath->query("//div[@class='product-item-container']");
	foreach ($data as $d) {
	
		$r["marq"] = $xpath->query(".//img[@class='logo-brand']", $d)->item(0)->getAttribute("alt");
		
		$product_name = $xpath->query(".//h2[@data-cerberus='listing-title']/a", $d)->item(0)->textContent;
		$r["prod"] = str_replace("Pneu&nbsp;".$r["marq"]."&nbsp;", "", htmlentities($product_name));
		
		//if ($xpath->query(".//a[@data-product-currency='EUR']", $d)->length == 0) continue; //non disponible
		//$price = $xpath->query(".//a[@data-product-currency='EUR']", $d)->item(0)->getAttribute("data-product-saleprice");
		// <span class="ws-amount kor-product-sale-price-value ws-sale-price" itemprop="price" data-cerberus="product-price">55,50 €</span>
		
		$r["pric"]  = $xpath->query(".//span[@itemprop='price']", $d)->item(0)->textContent;
		           
		$r["carb"] = $xpath->query(".//img[@alt='eco_carburant']/following-sibling::*[1]", $d)->item(0)->textContent;
		
		if ($r["carb"] == "") $r["carb"] = "-";
		$r["carb"] = "<img src='img/carb.png'/>" . strtoupper($r["carb"]);
		           
		$r["adhe"] = $xpath->query(".//img[@alt='eco_adherence']/following-sibling::*[1]", $d)->item(0)->textContent;
		if ($r["adhe"] == "") $r["adhe"] = "-";
		$r["adhe"] = "<img src='img/adherence.png'/>" . strtoupper($r["adhe"]);
		
		$r["nois"] = $xpath->query(".//img[@alt='eco_noise']/following-sibling::*[1]", $d)->item(0)->textContent;
		if ($r["nois"] == "") $r["nois"] = "-";
		$r["nois"] =  "<img src='img/noise.png'/>" . $r["nois"] . " dB";
		           
		$r["sais"] = $xpath->query(".//span[starts-with(@class,'sprite-meteo')]", $d)->item(0)->getAttribute("class");
		$r["sais"] = str_replace("sprite-meteo-", "", $r["sais"]);
		//$r["sais"] = $r_img[$r["sais"]];
		
		$spec = $xpath->query(".//div[@class='product-weather']/a", $d)->item(0)->getAttribute("href");
		$spec = explode("-", $spec);
		$nb = count($spec);
		for ($i=0;$i<$nb;$i++) {
			if (intval($spec[$i]) == $s_larg) {
				$r["larg"] = $spec[$i];
				$r["haut"] = $spec[$i+1];
				$r["diam"] = $spec[$i+2];
				$r["indi"] = $spec[$i+3];
				$r["vite"] = $spec[$i+4];
				$r["vite"] = preg_replace('/(_.+)/', '', $r["vite"]);
				break;
			}
		} 
		
		$r["dime"] = $r["larg"] . " / " . $r["haut"] . " " . strtoupper($r["diam"]) . " " . $r["indi"] . strtoupper($r["vite"]);
		$r["img_pneu"] = $xpath->query(".//img[@data-cerberus='product-thumbnail']", $d)->item(0)->getAttribute("src");
		$r["img_pneu_src"] = $xpath->query(".//img[@data-cerberus='product-thumbnail']", $d)->item(0)->getAttribute("src");
		$r["img_pneu_name"] = end(explode("/", $r["img_pneu_src"]));
		$r["img_pneu_path"] = "img/pneu/" . $r["img_pneu_name"];
		
		$r["img_pneu_src"] = "http:" . $r["img_pneu_src"];
		
		//exec("wget --no-check-certificate -o " . $r["img_pneu_path"] . " " .  $r["img_pneu_src"]);// . "<br>";
	
		
		to_html($r, $res);
		

	
		//$image = file_get_contents($r["img_pneu_src"]);
		//file_put_contents($r["img_pneu_path"], getContentUrl($r["img_pneu_src"]));
		//getContentUrl($r["img_pneu_path"], $r["img_pneu_src"]);
		
		//echo "<img src='".$r["img_pneu_path"]."'/>";
		// copy($r["img_pneu_src"], "img/pneu/" . $r["img_pneu_name"]);
		
		//to_html($r, $res);
		//break;
	}

	echo $res;

	//file_put_contents("data/$filename", $res);
}

function to_html(&$r, &$res) {

		$r_img["summer"] = "<img src='img/sun.png'/>";
		$r["sais"] = $r_img[$r["sais"]];
		
		$r["devis"] = "<button class='btn btn-success'>Mon devis</button>";
		

		$res .= "<div class='row mylist'>";
		$res .= "<div class='col-xs-3 col-sm-2 mywrapper myimg'><div class='myitem myimg'><img class='img-thumbnail' src='".$r["img_pneu"]."'/></div></div>";
		

		$select = array("sais", "marq", "prod", "dime");
		$res .= "<div class='col-xs-6 col-sm-3 mywrapper product'>";
		foreach($select as $s) $res .= "<div class='myitem $s'>".$r[$s]."</div>";
		$res .= "</div>";
		
		$select = array("carb","adhe", "nois");
		$res .= "<div class='col-xs-6 col-sm-4 mywrapper stats'>";
		foreach($select as $s) $res .= "<div class='myitem $s'>".$r[$s]."</div>";
		$res .= "</div>";
			
		$select = array("pric", "devis");
		$res .= "<div class='col-xs-12 col-sm-3 mywrapper price'>";
		foreach($select as $s) $res .= "<div class='myitem $s'>".$r[$s]."</div>";
		$res .= "</div>";
		
		$res .= "</div>";
		$res .= "<div class='sep'></div>";
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<link rel="stylesheet" href="js/bootstrap/bootstrap.min.css">
		<link rel="stylesheet" href="css/style.css">
		<script src="js/jquery/jquery.min.js"></script>
		<script src="js/bootstrap/bootstrap.min.js"></script>
		
		
	</head>
	<body>
	
	
	
		<div class="container mytitle">
			<div class="row mytitle">
				<div class="col-md-10">
					<br><br>
					<h1 class="mytitle">
						<span class="pull-right">
							<span class="color_25">PNEU </span>
							<span class="color_18">MOBILE</span>
						</span>
					</h1>
					<br><br><br><br><br><br>
					<h3>
						<span class="color_25">STATION DE MONTAGE DE PNEUS A </span>
						<span class="color_18">DOMICILE</span>
					</h3>
					<br>
					<h5 class="text-center">
						<span class="color_25">SECTEUR</span><br><br>
						<span class="color_25">DE</span><br><br>
						<span class="color_25">SARREBOURG - PHALSBOURG - SAVERNE - STRASBOURG</span>
				
					</h5>
					
				</div>
				
				<div class="col-md-2 man hidden-xs hidden-sm">
					<img src="img/man.png"/>
				</div>
				
			</div>
		</div>
		

		
		<div class="container mynav">
			<div id="custom-bootstrap-menu" class="navbar navbar-expand-lg navbar-default"" role="navigation">
				<div class="navbar-header hidden-sm hidden-md hidden-lg">
					<a class="navbar-brand" href="#">PNEU MOBILE</a>
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-menubuilder">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					</button>
				</div>
				<div class="collapse navbar-collapse navbar-menubuilder">
					<ul class="nav navbar-nav navbar-left">
						<li><a href="/">NOTRE VEHICULE</a>
						</li>
						<li><a href="/products">TARIF PNEUS</a>
						</li>
						<li><a href="/about-us">TARIF PRESTATIONS</a>
						</li>
						<li><a href="/contact">NOUS CONTACTER</a>
						</li>
						<li><a href="/contact">F.A.Q</a>
						</li>
					</ul>
				</div>
			</div>
		</div>

		
		<div class="container">
			<div class='sep'></div>
			<?php search($s_saison, $s_larg, $s_haut, $s_diam, $num_page);?>
		</div>
	</body>
</html>
