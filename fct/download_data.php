<?php


function mymain() {
	
        $l = 195;
        $h = 60;
        $d = 15

	//for ($l=130; $l<350; $l+=5)
	//for ($h=40; $h<100; $h+=5)
	//for ($d=13; $d<26; $d++)
	//echo "$l, $h, $d <br>";

	search("ete", $l, $h, $d, 1);
}

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
		$r["prod"] = trim(str_replace("Pneu&nbsp;".$r["marq"]."&nbsp;", "", htmlentities($product_name)));
		
		//if ($xpath->query(".//a[@data-product-currency='EUR']", $d)->length == 0) continue; //non disponible
		//$price = $xpath->query(".//a[@data-product-currency='EUR']", $d)->item(0)->getAttribute("data-product-saleprice");
		// <span class="ws-amount kor-product-sale-price-value ws-sale-price" itemprop="price" data-cerberus="product-price">55,50 €</span>
		
		$r["pric"]  = $xpath->query(".//span[@itemprop='price']", $d)->item(0)->textContent;
		           
		$r["carb"] = $xpath->query(".//img[@alt='eco_carburant']/following-sibling::*[1]", $d)->item(0)->textContent;
		
		if ($r["carb"] == "") $r["carb"] = "-";
		//$r["carb"] = "<img src='img/carb.png'/>" . strtoupper($r["carb"]);
		           
		$r["adhe"] = $xpath->query(".//img[@alt='eco_adherence']/following-sibling::*[1]", $d)->item(0)->textContent;
		if ($r["adhe"] == "") $r["adhe"] = "-";
		//$r["adhe"] = "<img src='img/adherence.png'/>" . strtoupper($r["adhe"]);
		
		$r["nois"] = $xpath->query(".//img[@alt='eco_noise']/following-sibling::*[1]", $d)->item(0)->textContent;
		if ($r["nois"] == "") $r["nois"] = "-";
		//$r["nois"] =  "<img src='img/noise.png'/>" . $r["nois"] . " dB";
		           
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
		
		exec("wget --no-check-certificate -O " . $r["img_pneu_path"] . " " .  $r["img_pneu_src"]);// . "<br>";
	
		
		$select = array("sais", "marq", "prod", "dime", "carb","adhe", "nois", "pric", "img_pneu_path");
		foreach($select as $k) {
			$res .= trim($r[$k]) . "|"; 
		}
		
		$res .= "\n";
	
	
		//$image = file_get_contents($r["img_pneu_src"]);
		//file_put_contents($r["img_pneu_path"], getContentUrl($r["img_pneu_src"]));
		//getContentUrl($r["img_pneu_path"], $r["img_pneu_src"]);
		
		echo "<img src='".$r["img_pneu_path"]."'/>";
		// copy($r["img_pneu_src"], "img/pneu/" . $r["img_pneu_name"]);
		
		//to_html($r, $res);
		//break;
	}
	
	file_put_contents("data/$filename", $res);
}

//search($s_saison, $s_larg, $s_haut, $s_diam, $num_page);

mymain();
?>
