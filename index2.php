<?php

error_reporting(E_ERROR | E_PARSE);


//$url = "https://www.norauto.fr/0/0/0/ete/0/195-65-15.html";

$s_saison = "ete";
$s_larg = "195";
$s_haut = "60";
$s_diam = "15";
$num_page = "2";


$res = "";

function search($s_saison, $s_larg, $s_haut, $s_diam, $num_page) {
	//$url = "https://www.norauto.fr/0/0/0/$s_saison/0/$s_larg-$s_haut-$s_diam.html?PageNumber=$num_page";
	//$file_content = file_get_contents($url);
	//file_put_contents("data.data", $file_content);
	
	$file_content = file_get_contents("data.data");
	
	$doc = new DOMDocument();
	$doc->loadHTML($file_content);
	$xpath = new DOMXpath($doc);
	
	$r = array();
	$r_img = array();
	
	$r_img["summer"] = "<img src='img/sun.png'/>";
	
	
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
		$r["carb"] = "<img src='img/carb.png'/>" . $r["carb"];
		           
		$r["adhe"] = $xpath->query(".//img[@alt='eco_adherence']/following-sibling::*[1]", $d)->item(0)->textContent;
		$r["adhe"] = "<img src='img/adherence.jpg'/>" . $r["adhe"];
		
		$r["nois"] = $xpath->query(".//img[@alt='eco_noise']/following-sibling::*[1]", $d)->item(0)->textContent;
		$r["nois"] =  "<img src='img/noise.png'/>" . $r["nois"] . " dB";
		           
		$r["sais"] = $xpath->query(".//span[starts-with(@class,'sprite-meteo')]", $d)->item(0)->getAttribute("class");
		$r["sais"] = str_replace("sprite-meteo-", "", $r["sais"]);
		$r["sais"] = $r_img[$r["sais"]];
		
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
		
		
		$res .= "<div class='row mylist'>";
		
		$res .= "<div class='col-xs-2 col-sm-1 mywrapper myimg'><div class='myitem myimg'><img class='img-thumbnail' src='img/thumb.jpg'/></div></div>";
		
		//$select = array("sais");
		//$res .= "<div class='col-xs-1 col-sm-1 mywrapper saison'>";
		//foreach($select as $s) $res .= "<div class='myitem $s'>".$r[$s]."</div>";
		//$res .= "</div>";
		
		//$select = array("sais", "marq", "prod", "larg","haut","diam","indi","vite");
		//$res .= "<div class='col-xs-6 col-sm-5 mywrapper product'>";
		//foreach($select as $s) $res .= "<div class='myitem $s'>".$r[$s]."</div>";
		//$res .= "</div>";
		
		
		$select = array("sais", "marq", "prod", "dime");
		$res .= "<div class='col-xs-6 col-sm-5 mywrapper product'>";
		foreach($select as $s) $res .= "<div class='myitem $s'>".$r[$s]."</div>";
		$res .= "</div>";
		
		//$select = array("larg","haut","diam");
		//$res .= "<div class='col-xs-2 col-sm-2 mywrapper dimension'>";
		//foreach($select as $s) $res .= "<div class='myitem $s'>".$r[$s]."</div>";
		//$res .= "</div>";
		
		$select = array("carb","adhe", "nois");
		$res .= "<div class='col-xs-2 col-sm-2 mywrapper stats'>";
		foreach($select as $s) $res .= "<div class='myitem $s'>".$r[$s]."</div>";
		$res .= "</div>";
		
		//$select = array("indi","vite");
		//$res .= "<div class='col-xs-1 col-sm-1 mywrapper charge'>";
		//foreach($select as $s) $res .= "<div class='myitem $s'>".$r[$s]."</div>";
		//$res .= "</div>";
		
		$select = array("pric");
		$res .= "<div class='col-xs-12 col-sm-2 mywrapper price'>";
		foreach($select as $s) $res .= "<div class='myitem $s'>".$r[$s]."</div>";
		$res .= "</div>";
		
		$res .= "</div>";
		
		$res .= "<div class='sep'></div>";		
	}
	
	echo $res;
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width-device=width, initial-scale=1" />
		<link rel="stylesheet" href="js/bootstrap/bootstrap.min.css">
		<link rel="stylesheet" href="css/style.css">
		<script src="js/jquery/jquery.min.js"></script>
		<script src="js/bootstrap/bootstrap.min.js"></script>
	</head>
	<body>
		<div class="container-fuild">
			<div class="row">
				<button class="btn btn-success">test</button>
				<h1 class="text-center">Pneu mobile</h1>
			</div>
		</div>
		
		<div class="container">
			<div class='sep'></div>
			<?php search($s_saison, $s_larg, $s_haut, $s_diam, $num_page);?>
		</div>
	</body>
</html>