<?php
function isPluginEnabled($pluginid){
  return file_exists(GSDATAOTHERPATH."$pluginid-enabled");
}

function EnablePlugin($pluginid){
 if (!isPluginEnabled($pluginid)){
   $fH = fopen(GSDATAOTHERPATH."$pluginid-enabled",'x');
   fwrite($fH,'enabled');
   fclose($fH);
 }
}

function DisablePlugin($pluginid){
 if (isPluginEnabled($pluginid)){
   unlink(GSDATAOTHERPATH."$pluginid-enabled");
 }
}

class DominionSimpleXML extends SimpleXMLElement{   
  public function addCData($cdata_text){   
    //obtained form  get-simple source
   $node= dom_import_simplexml($this);   
   $no = $node->ownerDocument;   
   $node->appendChild($no->createCDATASection($cdata_text));   
  } 
  
  public function updateCData($cdata_text){   
   $node= dom_import_simplexml($this);   
   $no = $node->ownerDocument;   
   $node->removeChild($node->firstChild);   
   $node->appendChild($no->createCDATASection($cdata_text));   
  } 
  
  public function removeCurrentChild(){
    $dom=dom_import_simplexml($this);
    $dom->parentNode->removeChild($dom);
  }
  
  public function XMLSave($file){
     //For future use if added funcitonaily is required
     $this->asXML($file);
  }

} 

function getDominionXML($file) {
    //obtained form  get-simple source
	$xml = @file_get_contents($file);
	$data = simplexml_load_string($xml, 'DominionSimpleXML', LIBXML_NOCDATA);
	return $data;
}

function getLanguageFile($plugin_name,$language,$basepath="") {
  if ($basepath == "") {
    return GSPLUGINPATH."dominion-it-shared/lang/$plugin_name/$language".'.php';
  } else {
    return $basepath."dominion-it-shared/lang/$plugin_name/$language".'.php';
  }  
}

function availableLanguages($plugin_name,$currLanguage = ''){
  /*
    This will return list of options for select box.
  */
  $files = scandir(GSPLUGINPATH."dominion-it-shared/lang/$plugin_name/" );
  $hoev = count($files);
  for ($x = 0;$x < $hoev ; $x++) {
    $file = $files[$x];
    if (($file <> '.') && ($file <> '..') && ($file <> '.htaccess')) {
      //is file
      $file = substr($file,0,strpos($file,'.'));
      if ($currLanguage == $file) {
        echo "<option value='$file' selected='selected'>$file</option>";
      } else {
        echo "<option value='$file'>$file</option>";
      }  
    }
  }
}

function getBaseSiteURLandAddChar(&$baseURL,&$addChar){
//get the base url for normal or FANCY URL's.. if fancy it will remove all added values and keep url string for normal it will insure the id gets kept
  $baseURL = $_SERVER["REQUEST_URI"];
  if (strpos($baseURL,'?id=') !== false) {
    $id = $_GET['id'];
    $baseURL = preg_replace("/\?id=.*/i","",$baseURL); 
    $baseURL .= "?id=$id";
    $addChar = '&';
  }  else {
    $addChar = '?';  
    $baseURL = preg_replace("/\?.*/i","",$baseURL); 
  }
}

function bouDatumCombos($dateValue,$targetDatBlock,$targetForm,$monthLanguageArray = null) {
  /*
  Build 3 combox that has the date time of current given date value
  And a hidden field where the complete date ACTUALLY gets stored to. ($targetDatBlock)
  */
  $jaar = date('Y',strtotime($dateValue));
  $maand = date('m',strtotime($dateValue));
  $dag = date('d',strtotime($dateValue));

  echo "<input type='hidden' value='$dag-$maand-$jaar' name='$targetDatBlock' />";
  echo "<select onchange='setDTForHidField($targetForm.$targetDatBlock,$targetForm.dt_year,$targetForm.dt_month,$targetForm.dt_day); stelMaandDae($targetForm.dt_year,$targetForm.dt_month,$targetForm.dt_day);' name='dt_month' id='dt_month'>";
  for ($xM=1;$xM <=  12;$xM++) {
    $mW =($xM < 10)?'0'.$xM:$xM;
    $selT = ($xM == $maand)?"selected='selected'":' ';
    if ($monthLanguageArray == null) {
      $Mt = date('F',strtotime("$xM/28/2000"));
    } else {
      $Mt = $monthLanguageArray[date('n',strtotime("$xM/28/2000"))];
    }    

    echo "<option value='$mW' $selT>$Mt</option>";
  }
  echo "</select>";
  echo "<select onchange='setDTForHidField($targetForm.$targetDatBlock,$targetForm.dt_year,$targetForm.dt_month,$targetForm.dt_day);' name='dt_day' id='dt_day'>";
  for ($xM=1;$xM <=  31;$xM++) {
    $mW =($xM < 10)?'0'.$xM:$xM;
    $selT = ($xM == $dag)?"selected='selected'":' ';
    echo "<option value='$mW' $selT>$mW</option>";
  }
  echo "</select>";
  echo "<select onchange='setDTForHidField($targetForm.$targetDatBlock,$targetForm.dt_year,$targetForm.dt_month,$targetForm.dt_day); stelMaandDae($targetForm.dt_year,$targetForm.dt_month,$targetForm.dt_day);' name='dt_year' id='dt_year'>";
  $huidigeJaar = date('Y');
  for ($xM=1900;$xM <=  $huidigeJaar;$xM++) {
    $mW =$xM;
    $selT = ($xM == $jaar)?"selected='selected'":' ';
    echo "<option value='$mW' $selT>$mW</option>";
  }
  echo "</select>";  
  

}

function getAllAvailableSlugs(){
 $dataPad = GSDATAPATH."pages/";
  $files = scandir($dataPad);
  $hoev = count($files);
  
  for ($x = 0;$x < $hoev ; $x++) {
    $file = $files[$x];
    if (($file <> '.') && ($file <> '..') && ($file <> '.htaccess')) {
      $Slugxml = getDominionXML($dataPad.$file);
      $dataBlok = $Slugxml->xpath("/item/url");
      $slugLys[] = $dataBlok[0][0];
    }
  }
  return $slugLys;
}
?>