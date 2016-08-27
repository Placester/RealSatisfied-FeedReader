<?
require_once('feedread.class.php'); 
?>
<!DOCTYPE html>
<html>
	<head>
		<title>RealSatisfied Feed Reader</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<style>
			*{font-family: monospace;}
			h5{font-size: 12px;}
			.l{display: block;font-weight: bold; font-size: 14px;margin-bottom: 15px;margin-top: 15px; }
			.v{font-weight: bold;}
			.e{display: block;}
			.i{display: block;}
			.s{margin-top: 15px; margin-bottom: 15px;}
			.item{margin-bottom: 20px;}
			label{color:blue;}
			error{color: red;}
		</style>
	</head>
	<body>
		<form method="get">
			<input name="v" required="required" placeholder="vanity_key" value="<?=$_GET["v"]?>" style="width:250px;" />
			<select name="t">
				<option value="V2" <? if(strtoupper($_GET["t"])=="V2"){echo " selected";}?>>Variable feed (based on Profile settings)</option>
				<option value="V1" <? if(strtoupper($_GET["t"])=="V1"){echo " selected";}?>>Aggregate ratings only</option>
				<option value="OFFICE" <? if(strtoupper($_GET["t"])=="OFFICE"){echo " selected";}?>>Office Feed</option>
				<option value="HAR" <? if(strtoupper($_GET["t"])=="HAR"){echo " selected";}?>>HAR (Texas)</option>
				<option value="RDC" <? if(strtoupper($_GET["t"])=="RDC"){echo " selected";}?>>RDC (US only)</option>
			</select>
			<input type="submit" value="Go" />
		</form>
<?
if(isset($_GET["v"])){
	$vanity_key = $_GET["v"];
}else{
	exit('<error>vanity_key is required</error>');
}
$feed_type = strtoupper($_GET["t"]);

$feedread = new feedread();
$datareturn = $feedread->get_data($vanity_key, $feed_type);

if($datareturn["status"]==1){
	$data = $datareturn["data"];
}else{
	exit($datareturn["message"] . ": ". $datareturn["data"]);
}

/* 
 * $data Array contains all data related to this feed.
 * $ratings Array contains name/val pairs for feed specific ratings 
 * $items Array contains the transaction specific information including ratings where available
 * use the names from the ratings array to reference the ratings in the $items array
 * all feeds contain a $data["summaryrating"] where ratings are available.
 * review the RealSatisfied XML name space for detail on specific data available : http://rss.realsatisfied.com/ns/realsatisfied/
 */

?>		
		<h1>Rating and Testimonial Data for <span class="v"><?=$data["name"];?></span></h1>
		<p>Version : <?=$data["version"];?></p>
		<h5>feed format: <?=$feedsource?></h5>
		<h5>review the RealSatisfied XML name space for detail on specific data available : <a href="http://rss.realsatisfied.com/ns/realsatisfied/" target="_blank">http://rss.realsatisfied.com/ns/realsatisfied/</a></h5>
		<div class="data">
			<div class="l">$data array</div>
<?		foreach ($data as $key => $val) {
			if($key!='items' && $key!='ratings'){
				if($key=='logo' || $key=='avatar'){
?>
			<div class="e"><label><?=$key;?></label> : <span class="v"><img src="<?=$val;?>"/></span></div>
<?			
				}else if(substr($val, 0, 4)=="http"){
?>
			<div class="e"><label><?=$key;?></label> : <span class="v"><a href="<?=$val;?>" target="_blank"><?=$val;?></a></span></div>
<?			
				}else{
?>
			<div class="e"><label><?=$key;?></label> : <span class="v"><?=$val;?></span></div>
<?					
				}
			}
		}
		if(sizeof($data["ratings"])>0){
?>
			<div class="l">$ratings array</div>
<?			
			foreach ($data["ratings"] as $rating) {
?>
			<div class="e"><label><?=$rating["name"];?></label> : <span class="v"><?=$rating["score"];?></span></div>
<?			
			}
		}
		
?>
			<blockquote><div class="l">$items array (<?=sizeof($data["items"])?> items)</div>
<?				
			foreach ($data["items"] as $item) {
?>
				<div class="item">
<?				
				foreach ($item as $ikey => $ival) {
					if($ikey=='avatar'){
?>	
				<div class="i"><label><?=$ikey;?></label> : <span class="v"><img src="<?=$ival;?>"/></span></div>
<?				
					}else{
?>	
				<div class="i"><label><?=$ikey;?></label> : <span class="v"><?=$ival;?></span></div>
<?										
					}
				}
?>
				</div>
<?				
			}
?>
			</blockquote>
		</div>
	</body>
</html>

