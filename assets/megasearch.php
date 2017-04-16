<?php


$passw = 'CZ.G7Xr9Lb4#kSYEzY:w';
$host = "db598019927.db.1and1.com";
$user = "dbo598019927";
$database = "db598019927";

$passw = 'agro';
$host = "localhost";
$user = "root";
$database = "local_hvb";

#$verbindung = mysql_connect($host,$user,$passw);
#mysql_set_charset('utf8',$verbindung);
#mysql_select_db($db) or die ("Die Datenbank existiert nicht.");

$db = new mysqli($host,$user,$passw,$database);
if($db->connect_errno > 0){
    die('Unable to connect to database [' . $db->connect_error . ']');
}
$db->set_charset('utf8');
//mysqli_set_charset($db,"utf8");

if($_REQUEST['ort'] != "" and $_REQUEST['ort'] != null){
	$isNumber = false;
	$hasNull = false;
	$withPLZ = false;
//	$request = mb_strtolower($_REQUEST['ort'],'UTF-8');
    $request = htmlspecialchars($_REQUEST['ort'], ENT_QUOTES, 'UTF-8');
    $request = mb_strtolower($request,'UTF-8');
	$plz = NULL;
	$ort = NULL;
	$sql = NULL;
	$sql_raw = NULL;
	$stmt = NULL;

	//////////////////////////////////////////////////////
	// CLEAN UP REQUEST
	//////////////////////////////////////////////////////

	if (!preg_match("/^([a-zA-Z0-9öäüßÖÄÜß,.() \n\r-]+)$/is", $request)) {
		//$errorArray = array('ort' => "Unerlaubte Zeichen enthalten!");
		$errorArray = array();
		$tmpInsert  = array();
		$tmpInsert['ort'] = "Unerlaubte Zeichen enthalten!";
		$errorArray[] = $tmpInsert;
        $myString = date('Ymd H:i')."::".$_SERVER['REMOTE_ADDR']."::".$request."\n";
        $file = 'log-inputs.txt';
        file_put_contents($file, $myString, FILE_APPEND);
		echo json_encode($errorArray, JSON_PRETTY_PRINT);
		//mysql_close($verbindung);
        mysqli_close($db);
	 	die();
	}

	//////////////////////////////////////////////////////
	// FIRST PLZ
	//////////////////////////////////////////////////////

	$suchmuster_plz = '/^([0]{1}|[1-9]{1})/';
	if(preg_match($suchmuster_plz, $request)){
		$isNumber = true;
		$splitAnfrage = explode(' ',$request);
		$requestPLZ = $splitAnfrage[0];
		// check leading ZERO
		$suchmuster_plz = '/^([0])/';
		if(preg_match($suchmuster_plz, $requestPLZ)){
			$requestPLZ = substr($requestPLZ, 1);
			$hasNull = true;
			// build searchSTring like "40___"
			for( $i=strlen($requestPLZ);$i<4;$i++){
				$requestPLZ .= '_';
			}
		}else{
			// searchString like "404%"
			$requestPLZ .= '%';
		}

		// cut off plz
		unset($splitAnfrage[0]);
		// build up rest of string to ortString
		$requestOrt = implode(' ',$splitAnfrage);
		$requestOrt = strtolower($requestOrt)."%";

		$sql = "select distinct plzS as post, question as value, isFamus from tl_hvz inner join tl_plz on tl_hvz.id = tl_plz.ortid where tl_plz.plzS like '".$requestPLZ."' and LOWER(tl_hvz.question) like '".$requestOrt."' group by question order by isFamus DESC ,question ASC   LIMIT 0, 10";
        $sql_raw = $sql;
	}

	//////////////////////////////////////////////////////
	// FIRST ORT
	//////////////////////////////////////////////////////

	//$musterString = '/^[a-zA-Z]*/';
    $musterString = '/^[a-z-A-Z]/';
	if(preg_match($musterString, $request)){
		$splitAnfrage = explode(' ',$request);
		// plz unknown
		$requestPLZ = '%';
		$requestOrt = '';
		foreach ($splitAnfrage as $value) {
			// find a number as plz
			$suchmuster_plz = '/^([0]{1}|[1-9]{1})/';
			if(preg_match($suchmuster_plz, $value)){
				$withPLZ = true;
				$requestPLZ = $value;
				$suchmuster_plz = '/^([0])/';
				if(preg_match($suchmuster_plz, $requestPLZ)){
					$requestPLZ = substr($requestPLZ, 1);
					$hasNull = true;
					for( $i=strlen($requestPLZ);$i<4;$i++){
						$requestPLZ .= '_';
					}
				}else{
					$requestPLZ .= '%';
				}
				break;
			}
			$requestOrt .= $value." ";
		}
		// cut off last whitespace
		$requestOrt = substr($requestOrt,0,-1);
		$requestOrt = strtolower($requestOrt)."%";

		// get alternative ortStrings
		$umlaute  = array("ü","ö","ä");
		$umlautev = array("ue","oe","ae");
		$request_alt0 = str_replace($umlautev, $umlaute, $requestOrt);
		$request_alt1 = str_replace($umlaute, $umlautev, $requestOrt);

		$request_alt2 = str_replace('ss', 'ß', $request_alt0);
		$request_alt3 = str_replace('ß', 'ss', $request_alt0);

		$request_alt4 = str_replace('ss', 'ß', $requestOrt);
		$request_alt5 = str_replace('ß', 'ss', $requestOrt);

		$sql = "select distinct plzS as post, question as value, land, isFamus from tl_hvz inner join tl_plz on tl_hvz.id = tl_plz.ortid where tl_plz.plzS like '".$requestPLZ."' and ( LOWER(question) like '".$requestOrt."' or LOWER(question) like '".$request_alt1."' or LOWER(question) like '".$request_alt2."' or LOWER(question) like '".$request_alt0."' or LOWER(question) like '".$request_alt3."' or LOWER(question) like '".$request_alt4."' or LOWER(question) like '".$request_alt5."' ) group by question order by isFamus DESC ,question ASC LIMIT 0, 5;";

# add ausland
		//$sql_raw = "select distinct question as value, land, isFamus from tl_hvz where ( LOWER(question) like '".$requestOrt."' or LOWER(question) like '".$request_alt1."' or LOWER(question) like '".$request_alt2."' or LOWER(question) like '".$request_alt0."' or LOWER(question) like '".$request_alt3."' or LOWER(question) like '".$request_alt4."' or LOWER(question) like '".$request_alt5."' ) group by question order by isFamus DESC ,question ASC LIMIT 0, 5;";
        $sql_raw = "select distinct '' as post, question as value, land, isFamus from tl_hvz where ( LOWER(question) like '%".$requestOrt."' or LOWER(question) like '".$requestOrt."' or LOWER(question) like '".$request_alt1."' or LOWER(question) like '".$request_alt2."' or LOWER(question) like '".$request_alt0."' or LOWER(question) like '".$request_alt3."' or LOWER(question) like '".$request_alt4."' or LOWER(question) like '".$request_alt5."' ) group by question order by isFamus DESC ,question ASC LIMIT 0, 5;";
        //echo "firstOrt:".$sql."\n\n";

	}

	$result = $db->query($sql);
	$result_raw = $db->query($sql_raw);


	$emparray = array();
	$emparray_raw = array();

	$justValue = array();
	$db->close();
	
	while($newPlz = $result_raw->fetch_assoc()){
		$tmp = array();
		// PLZ first
		if($isNumber){
			$newPlz['post'] = ($hasNull) ? "0".$newPlz['post']: $newPlz['post'];
			$tmp['ort'] = $newPlz['post']." ".$newPlz['value'];
			$justValue[] = $newPlz['post']." ".$newPlz['value'];
		}else{
			if($withPLZ){
				$newPlz['post'] = ($hasNull) ? "0".$newPlz['post']: $newPlz['post'];
				$tmp['ort'] = $newPlz['value']." (".$newPlz['post'].")";
				$justValue[] = $newPlz['value']." (".$newPlz['post'].")";
			}else{
				$tmp['ort'] = $newPlz['value'];
				if ($newPlz['land'] != 'Deutschland'){
					//$tmp['ort'] = $tmp['ort'].' ('.$newPlz['land'].')';
				}
				$justValue[] = $newPlz['value'];
			}
		}
		$tmp['ort'] = str_replace('&#40;', '(', $tmp['ort']);
		$tmp['ort'] = str_replace('&#41;', ')', $tmp['ort']);
		$emparray_raw[] = $tmp;
	}

	
	while($newPlz = $result->fetch_assoc()){
		$tmp = array();
		// PLZ first
		if($isNumber){
			$newPlz['post'] = ($hasNull) ? "0".$newPlz['post']: $newPlz['post'];
			$tmp['ort'] = $newPlz['post']." ".$newPlz['value'];
			$justValue[] = $newPlz['post']." ".$newPlz['value'];
		}else{
			if($withPLZ){
				$newPlz['post'] = ($hasNull) ? "0".$newPlz['post']: $newPlz['post'];
				$tmp['ort'] = $newPlz['value']." (".$newPlz['post'].")";
				$justValue[] = $newPlz['value']." (".$newPlz['post'].")";
			}else{
				$tmp['ort'] = $newPlz['value'];
				$justValue[] = $newPlz['value'];
			}
		}
		$tmp['ort'] = str_replace('&#40;', '(', $tmp['ort']);
		$tmp['ort'] = str_replace('&#41;', ')', $tmp['ort']);
		$emparray[] = $tmp;
	}

	$allErg = array_unique(array_merge($emparray,$emparray_raw), SORT_REGULAR);

	header('Content-Type: application/json');
	echo json_encode($allErg, JSON_PRETTY_PRINT);
	//echo json_encode($justValue, JSON_PRETTY_PRINT);
	//mysql_close($verbindung);
}
