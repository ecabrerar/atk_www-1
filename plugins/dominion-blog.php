<?php
/*
Plugin Name: Dominion Blog
Description: Blog system that allows customization and uses XSL and XML for speed and low server resources
Version: 0.5a
Author: Dominion IT (Johannes Pretorius)
Author URI: http://www.dominion-it.co.za/
*/

/*
GLOBALS
*/
$dominion_blogs_data_path = GSPLUGINPATH."dominion-blog/data/";
$dominion_blogs_blogs_path = GSPLUGINPATH."dominion-blog/blogs/";
$dominion_blogs_group_file = 'dominion_blogs_groups.xml';
$dominion_blogs_settings_file = 'dominion_blogs_settings.cfg';



require_once("dominion-it-shared/dominion-common.php");

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile, 	# ID of plugin, should be filename minus php
	'Dominion Blog', 	# Title of plugin
	'0.5a', 		# Version of plugin
	'Johannes Pretorius',	# Author of plugin
	'http://www.dominion-it.co.za/', 	# Author URL
	'Blog/News system that allows customization in CSS', 	# Plugin Description
	'pages', 	# Page type of plugin
	'dominion_blog_show_config'  	# Function that displays content
);

# activate filter
add_filter('content','content_dominion_blog_show'); 
add_action('pages-sidebar','createSideMenu',array($thisfile,'Dominion Blog'));
add_action('theme-header','dominion_blog_header');

/*
  Filter Content for blog markers (%blog:blog_id%)
    the add of that id will be inserted in the markers section of the conent
*/
function content_dominion_blog_show($contents){
     if (!isPluginEnabled('dominion-blog')) {
       return $contents;
     }
     //we will do special stuff if it is a news item.
     $dblog_newsitem = isset($_GET['dblog_newsitem'])?$_GET['dblog_newsitem']:0; 
     
     if ($dblog_newsitem === 0) {
        $tmpContent = $contents;
    	preg_match_all('/\(%(.*)blog(.*):(.*)%\)/i',$tmpContent,$tmpArr,PREG_PATTERN_ORDER);
        
        $AlltoReplace = $tmpArr[count($tmpArr)-1];
        $totalToReplace = count($AlltoReplace);
        for ($x = 0;$x < $totalToReplace;$x++) {
           $targetBlog= str_replace('&nbsp;',' ',$AlltoReplace[$x]);
           $targetBlog = trim($targetBlog);
           
          $adTeks = dominion_blog_dump($targetBlog);
          $tmpContent = preg_replace("/\(%(.*)blog(.*):(.*)$targetBlog(.*)%\)/i",$adTeks,$tmpContent);
        }
    } else {
      //we replace everything with the news items contents
      //TODO more news code for news list etc etc.. blah blah..
      $dblog_newsitem = urldecode($dblog_newsitem);
      $tmpContent = dominion_blog_dump($dblog_newsitem);
    }
  return $tmpContent;
}

function saveIndexesForBlog(&$blogXML,$BlogFile){
 global $dominion_blogs_blogs_path;
//INDEX DATA
    $activeBlogItem = $blogXML->xpath("//id");
    if (count($activeBlogItem) > 0) {
      //only if there is something to index.
      $teller = count($activeBlogItem);
      
      //First get list of all ID's and tehre dates
      for ($tmpX = 0;$tmpX < $teller;$tmpX++) {
        $atr = $activeBlogItem[$tmpX]->attributes();
        $bID = (integer)$atr['blog_id'];
        $bDT = stripslashes($activeBlogItem[$tmpX]->blog_date);
        $bDT = strtotime($bDT);
        $curDataIndex[$bID] = $bDT;
      }
      
      //Now sort them
      arsort($curDataIndex,SORT_REGULAR);
      $XMLIndex = $blogXML->xpath("//dataindex");
      if (count($XMLIndex) <= 0) {
            $script = $blogXML->addChild('dataindex');
            $blok = '';
            $script_info = $script->addChild('index');
            $script_info->addCData(@$blok);
            $blogXML->XMLSave($dominion_blogs_blogs_path.$BlogFile.".xml");
            $XMLIndex = $blogXML->xpath("//dataindex");
      }
      $indexCSV = '';
      $totaal = count($curDataIndex);
      $teller = 0;
      foreach ($curDataIndex as $key => $val) {
         $indexCSV .= $key;
         $teller++;
         if ($totaal !=  $teller) { 
           $indexCSV .= ',';
         } 
      }
      $XMLIndex[0]->index->updateCData(@$indexCSV);  
      $blogXML->XMLSave($dominion_blogs_blogs_path.$BlogFile.".xml");              
    }
    //END INDEX DATA
}

function dominion_blog_handle_save_events(&$activeGroup,&$xml,&$activeBlogID){
global $dominion_blogs_data_path;
global $dominion_blogs_blogs_path;
global $dominion_blogs_group_file;
      //delete category and products
    if (isset($_GET['delgroupid'])) {
       $delGroup = $_GET['delgroupid'];
       deleteGroupandBlogs($delGroup);
       $activeGroup = -1;
      
    }

    //add New category
    if (isset($_GET['gid'])) {
      $activeGroup = createDefaultGroupAndBlog("example-blog".rand(1,234234));
      
    }
    
    //is there aproduct to edit
    if (isset($_GET['blogedit']) && isset($_GET['groupid'])) {
       $activeBlogID = $_GET['blogedit'];
    }
    
    if(isset($_POST['stoorgroep']) && $_POST['stoorgroep'] == 'Save Group') {
       $group_id = $_POST['groupid'];
       $xml = getDominionXML($dominion_blogs_data_path.$dominion_blogs_group_file);
       $activeItem = $xml->xpath("//id[@group_id=\"$group_id\"]");
       $atr = $activeItem[0]->attributes();
       $atr['group_name'] = stripslashes($_POST['group_name']);
       
       $xml->XMLSave($dominion_blogs_data_path.$dominion_blogs_group_file);
    } else if(isset($_POST['stoor_blog']) ) {
            $group_id = $_POST['groupid'] ;
            $blog_id = $_POST['blogid'] ;
            $xml = getDominionXML($dominion_blogs_data_path.$dominion_blogs_group_file);
            $activeItem = $xml->xpath("//id[@group_id=\"$group_id\"]");
            $blogXML = getBlogXMLObject($activeItem,$BlogFile);
            $activeBlogItem = $blogXML->xpath("//id[@blog_id=\"$blog_id\"]");
            $activeBlogItem[0]->blog_contents->updateCData(stripslashes($_POST['blog_contents']));
            $activeBlogItem[0]->blog_short->updateCData(stripslashes($_POST['blog_short']));
            $activeBlogItem[0]->blog_title->updateCData(stripslashes($_POST['blog_title']));            
            $activeBlogItem[0]->blog_date->updateCData(stripslashes($_POST['blog_date']));                        
            $blogXML->XMLSave($dominion_blogs_blogs_path.$BlogFile.".xml");
            saveIndexesForBlog($blogXML,$BlogFile);
            unset($blogXML,$activeItem,$activeBlogItem);
    } else if (is_file($dominion_blogs_data_path.$dominion_blogs_group_file)) {
          $xml = getDominionXML($dominion_blogs_data_path.$dominion_blogs_group_file);
    } else {
         $activeGroup  = createDefaultGroupAndBlog('example-blog');
         $xml = getDominionXML($dominion_blogs_data_path.$dominion_blogs_group_file);
    }
}

function dominion_blog_show_config(){


global $SITEURL;
global $dominion_blogs_data_path;
global $dominion_blogs_blogs_path;
global $dominion_blogs_group_file;

    $activeGroup = isset($_REQUEST['groupid'])?$_REQUEST['groupid']:-1;
    $activeBlogID = isset($_REQUEST['blogid'])?$_REQUEST['blogid']:-1;
    $adminID = $_GET['id'];
    //enabdle -- disable
    if (isset($_POST['dominion-enable'])) {
      EnablePlugin('dominion-blog');
    } else  if (isset($_POST['dominion-disable'])) {
      DisablePlugin('dominion-blog');
    }
    dominion_blog_handle_save_events($activeGroup,$xml,$activeBlogID);
  
    if ($activeGroup <= 0) {
      $activeItem = $xml->xpath("//id");
      $tmpAttr = $activeItem[0]->attributes();
      $activeGroup = $tmpAttr['group_id'];
    }    
    $activeItem = $xml->xpath("//id[@group_id=\"$activeGroup\"]");
    $atrTmp = $activeItem[0]->attributes();
    $ActivegroupName = $atrTmp['group_name'];
    unset($atrTmp);
    
    //add new blog
    if (isset($_GET['addnewblog']) && isset($_GET['groupid'])) {
       $activeBlogID = addNewBlog($activeItem);
    }
    
    //delete entry
    if (isset($_GET['blogdelete']) && isset($_GET['groupid'])) {
       $DeleteBlogID = $_GET['blogdelete'];
       deleteBlog($activeItem,$DeleteBlogID);
    }    
    
      $activeBlogItem = getBlogForEditing($activeItem,$activeBlogID);
      $prodAttr = $activeBlogItem->attributes();
      $blogID = $prodAttr['blog_id'];
      $activeBlogID = $blogID;
      $blog_contents = stripslashes($activeBlogItem->blog_contents);
      $blog_short = stripslashes($activeBlogItem->blog_short);
      
      $blog_title = stripslashes($activeBlogItem->blog_title);
      $blog_date = stripslashes($activeBlogItem->blog_date);
      
      $curItem = $xml->xpath("//id");
      
      $numGroups  = count($curItem);
    
?>
<script type="text/javascript" >
function setDTForHidField(hidFieldName,jaar,maand,dag) {
  hidFieldName.value = dag.value+'-'+maand.value+'-'+jaar.value;

}

function stelMaandDae(jaarVeld,maandVeld,dagVeld){
  var mV = maandVeld.value - 1;
  var jV = jaarVeld.value;
  var dV = daeIndiemaand(mV,jV);
  var huidigeDag = dagVeld.value;
  dagVeld.options.length=0;
  for(x=1;x <= dV;x++){
    if (huidigeDag == x) {
      dagVeld.options[dagVeld.options.length]=new Option(x,x,true,true);
    } else {
     dagVeld.options[dagVeld.options.length]=new Option(x,x,false,false);
   }  
  }
  
}

function daeIndiemaand(tMaand, tJaar){
	return 32 - new Date(tJaar, tMaand, 32).getDate();
}
  
</script>
<?php
  $dominion_blogs_active_language = 'en_US';
  global $dominion_blogs_settings_file;
  if (isset($_POST['stoor_settings']) ) {
     //save settings (in array 
     $setAr[0] = isset($_POST['date_graphics'])?$_POST['date_graphics']:0;
     $setAr[1] = $_POST['taal'];
     $setAr[2] = $_POST['news_items'];
     $setAr[3] = $_POST['blog_items'];
     $setAr[4] = $_POST['show_short_in_news'];
     $setAr[5] = $_POST['news_page'];
     $setAr[6] = $_POST['show_blog_summary'];
     $saveStr = implode(',',$setAr);
     file_put_contents($dominion_blogs_data_path.$dominion_blogs_settings_file,$saveStr);
     unset($saveStr,$setAr);
  }
  $settingsStr = file_get_contents($dominion_blogs_data_path.$dominion_blogs_settings_file);
  $settingsArr = explode(',',$settingsStr);
  $dominion_blogs_active_language = $settingsArr[1];
  
  $graphDate = ($settingsArr[0] == 1)?"checked='checked'":'';
  $show_news_short = isset($settingsArr[4]) && ($settingsArr[4] == 1)?"checked='checked'":'';
  $news_items = isset($settingsArr[2])?$settingsArr[2]:5;
  $blog_items = isset($settingsArr[3])?$settingsArr[3]:5;
  $show_blog_summary = isset($settingsArr[6]) && ($settingsArr[6] == 1)?"checked='checked'":'';
  include getLanguageFile('dominion-blog',$dominion_blogs_active_language);
?>

<div id='dominion_blog_header_tingie'><p><a id='dominion_blog_blogs_kliek' href='#blogs' ><?php echo $dominion_blog_general['SYSTEM_ITEM_BLOGS']; ?></a> | <a id='dominion_blog_css_kliek' href='#css'><?php echo $dominion_blog_general['SYSTEM_ITEM_CSS']; ?></a>| <a id='dominion_blog_settings_kliek' href='#settings'><?php echo $dominion_blog_general['SYSTEM_ITEM_SETTINGS']; ?></a></p></div>
<div id ='dominion_blog_settings' style='display:none;'>
<p><?php echo $dominion_blog_general['SETTINGS_HEADER']; ?>
    </p>
    <form action='<?php	echo $SITEURL."admin/load.php?id=$adminID";?>#settings' method='POST'>
    <p><b><?php echo $dominion_blog_general['SETTINGS_SUB_HEADER'];?></b></p>
    <table width='95%'>
    <tr>
    <th><?php echo $dominion_blog_general['SETTINGS_GRAPHICS_DATES'];?></th>
    <td><input type='checkbox' name='date_graphics' value='1' <?php echo $graphDate; ?>></td>
    </tr>
    <tr>
    <th><?php echo $dominion_blog_general['SETTINGS_SHOW_SHORT_IN_NEWS'];?></th>
    <td><input type='checkbox' name='show_short_in_news' value='1' <?php echo $show_news_short; ?>></td>
    </tr>
    <tr>
    <th><?php echo $dominion_blog_general['SETTINGS_SHOW_BLOG_SUMMARY'];?></th>
    <td><input type='checkbox' name='show_blog_summary' value='1' <?php echo $show_blog_summary; ?>></td>
    </tr>
    
    <tr>
    <th><?php echo $dominion_blog_general['SETTINGS_NUM_NEWS_ITEMS'];?></th>
    <td><input type='input' name='news_items' value='<?php echo $news_items; ?>'></td>
    </tr>
    <tr>
    <th><?php echo $dominion_blog_general['SETTINGS_NUM_BLOG_ITEMS'];?></th>
    <td><input type='input' name='blog_items' value='<?php echo $blog_items; ?>'></td>
    </tr>    
    <tr>
    <th><?php echo $dominion_blog_general['SETTINGS_LANGUAGES'];?></th>
    <td><select name='taal'>
    <?php availableLanguages('dominion-blog',$settingsArr[1]); ?>
    </select></td>
    </tr>
    <tr>
    <th><?php echo $dominion_blog_general['SETTINGS_NEWS_TARGET_PAGE'];?></th>
    <td>
<?php
          $pageList = getAllAvailableSlugs();
          $pageList[] = "[no-target-page]";
          $numPages = count($pageList);
    echo " <select name='news_page'>";
    for ($x = 0;$x < $numPages;$x++){
      $page_slug = $pageList[$x];
      if ($page_slug == $settingsArr[5] ) {
         echo "<option value='$page_slug' selected='selected'>$page_slug</option>";
      } else {
        echo "<option value='$page_slug'>$page_slug</option>";
      }  
    }
    echo "</select> "; 
?>      
    </td>     
    </tr>
        
    <tr>
    <th colspan='2'><center><input type='submit' name='stoor_settings' value='<?php echo $dominion_blog_general['SYSTEM_ITEM_BUTTON_SAVE']; ?>'></center></th>
    </tr>
    </table>
    
    </form>
</div>
<div id='dominion_blog_blogs' >
<form action="<?php	echo $SITEURL."admin/load.php?id=$adminID";?>"  method="post" id="management">
<?php
  if (isPluginEnabled('dominion-blog')) {
?>
  <p><b><?php echo $dominion_blog_general['PLUGIN_DISABLE']; ?>    </b><input type='checkbox' name='dominion-disable' value = '1' onclick='submit();'> </p>
<?php    
    } else {
?>
   <p><b> <?php echo $dominion_blog_general['PLUGIN_ENABLE']; ?>    </b><input type='checkbox' name='dominion-enable' value = '1' onclick='submit();'> </p>
<?php
 }
?>   
</form>
	<p><?php echo $dominion_blog_general['BLOG_HEADER']; ?> <a href='http://www.dominion-it.co.za/'>Dominion IT</a>  - (V 0.5a)</p>
<form action="<?php	echo $SITEURL."admin/load.php?id=$adminID";?>"  method="post" id="group_blog_management">
   <input type='hidden' name='groupid' value='<?php echo $activeGroup;?>'>
 <?php
echo "<p>".$dominion_blog_general['SYSTEM_ITEM_CURRENT_GROUPS']." : <select onchange='window.location = \"".$SITEURL."admin/load.php?id=$adminID&groupid=\"+this.value'>";
    for ($x=0;$x<$numGroups;$x++){
      $atr = $curItem[$x]->attributes();
      $sID = $atr['group_id'];
      $groupName = $atr['group_name'];
      $sName = stripslashes($groupName);
      if ($sID == $activeGroup ) {
          $aktieweGroep = $sName;
         echo "<option value='$sID' selected='selected'>$sName</option>";
      } else {
        echo "<option value='$sID'>$sName</option>";
      }  
    }
    echo "</select> <a href='".$SITEURL."admin/load.php?id=$adminID&gid=NewGroup'>".$dominion_blog_general['SYSTEM_ITEM_LINK_ADD_GROUP']."</a></p>"; 
 ?>
    <p><?php echo $dominion_blog_general['BLOG_NAME']; ?> <input type='text' name='group_name' value='<?php echo  $aktieweGroep; ?>'><?php if ($numGroups > 1) { ?> <a href='<?php echo $SITEURL; ?>admin/load.php?id=<?php echo $adminID;?>&delgroupid=<?php echo $activeGroup;?>' onclick="return confirm('All Blogs linked to Group will also be deleted. Are you sure ?');"><?php echo $dominion_blog_general['SYSTEM_ITEM_LINK_DELETE_GROUP']; ?></a> <?php } ?>
     <input type='submit' name='stoorgroep' value='<?php echo $dominion_blog_general['SYSTEM_ITEM_BUTTON_SAVE_GROUP']; ?>'></p>
    
</form>
<p><?php echo $dominion_blog_general['BLOG_INFO_COPY']; ?> <b>(% blog:<?php echo $ActivegroupName;?> %)</b><?php echo $dominion_blog_general['BLOG_INFO_LINE1']; ?> </p>
<p><?php echo $dominion_blog_general['BLOG_INFO_COPY']; ?> <?php  highlight_string("<?php dominion_news_show('$ActivegroupName'); ?>"); ?> <?php echo $dominion_blog_general['BLOG_INFO_LINE2']; ?> </p>
<hr/>
<?php echo "<a href='".$SITEURL."admin/load.php?id=$adminID&addnewblog=1&groupid=$activeGroup'>".$dominion_blog_general['SYSTEM_ITEM_LINK_ADD_BLOG']."</a>"; ?>;
<br/>
    
<form action="<?php	echo $SITEURL."admin/load.php?id=$adminID"?>"  method="post" id="dominion_blog_form" >
<input name='groupid' value='<?php echo $activeGroup ;?>' type='hidden'>
<input name='blogid' value='<?php echo $activeBlogID ;?>' type='hidden'>
<div>
  <p><?php echo $dominion_blog_general['BLOG_INFO_TITLE']; ?><input type='text' name='blog_title' value='<?php echo $blog_title; ?>'></p>
  <p><?php echo $dominion_blog_general['BLOG_INFO_DATE']; ?><?php echo  bouDatumCombos($blog_date,'blog_date','dominion_blog_form',$dominion_blog_months); ?></p>
  <b><?php echo $dominion_blog_general['BLOG_INFO_EXCERPT']; ?></b>
</div>  
  <div style='border:1px solid black;'> 
 <textarea id="blog_short" name="blog_short" style='border:1px solid black;'><?php echo $blog_short; ?></textarea><br/>
 </div>
 <div> <b><?php echo $dominion_blog_general['BLOG_INFO_BLOG']; ?></b> </div>
  <div style='border:1px solid black;'> 

 <textarea id="blog_contents" name="blog_contents" style='border:1px solid black;'><?php echo $blog_contents; ?></textarea><br/>
 </div>
    <p><input type='submit' name='stoor_blog' value='<?php echo $dominion_blog_general['SYSTEM_ITEM_BUTTON_SAVE']; ?>'></p>
</form>
<?php
    buildBlogsList($activeItem);
?>
</div>



<?php
  if (isset($_POST['stoor_css']) ) {
    $cssData = $_POST['css_template'];
    file_put_contents($dominion_blogs_data_path."dominion_css.css",$cssData);
  }
  $cssData = file_get_contents($dominion_blogs_data_path."dominion_css.css");
?>
<div id ='dominion_blog_css' style='display:none;'>
<p><?php echo $dominion_blog_general['CSS_HEADER']; ?><br/>
    <b>#dominion_blog_footer</b> - <?php echo $dominion_blog_general['CSS_ID_BLOG_FOOTER']; ?><br/>
    <b>#dominion_blog_header</b> - <?php echo $dominion_blog_general['CSS_ID_BLOG_HEADER']; ?><br/>
    <b>#dominion_blog_data</b> - <?php echo $dominion_blog_general['CSS_ID_BLOG_BODY']; ?><br/>
    <b>#dominion_news_header</b> - <?php echo $dominion_blog_general['CSS_ID_NEWS_HEADER']; ?><br/>
    <b>#dominion_news_data</b> - <?php echo $dominion_blog_general['CSS_ID_NEWS_BODY']; ?><br/>
    <b>#dominion_news_footer</b> - <?php echo $dominion_blog_general['CSS_ID_NEWS_FOOTER']; ?><br/>
    
    </p>
    <form action='<?php	echo $SITEURL."admin/load.php?id=$adminID";?>#css' method='POST'>
    <p><b><?php echo $dominion_blog_general['CSS_SUB_HEADER']; ?></b></p>
    <textarea id='css_template' name='css_template'><?php echo $cssData; ?></textarea>
    <input type='submit' name='stoor_css' value='<?php echo $dominion_blog_general['SYSTEM_ITEM_BUTTON_SAVE']; ?>'>
    
    </form>
</div>



<script type="text/javascript" >
   $("#dominion_blog_blogs_kliek").click(function(){
      $("#dominion_blog_css").hide();
      $("#dominion_blog_settings").hide();
      $("#dominion_blog_blogs").show("fast");
      //return false;
   });
   $("#dominion_blog_css_kliek").click(function(){
      $("#dominion_blog_blogs").hide();
      $("#dominion_blog_settings").hide();
      $("#dominion_blog_css").show("fast");
      //return false;
   });
   $("#dominion_blog_settings_kliek").click(function(){
      $("#dominion_blog_blogs").hide();
      $("#dominion_blog_css").hide();
      $("#dominion_blog_settings").show("fast");
      //return false;
   });   
   
  if (window.location.href.indexOf('#css') > 1) {
      $("#dominion_blog_blogs").hide();
      $("#dominion_blog_settings").hide();
      $("#dominion_blog_css").show("fast");
  }
  if (window.location.href.indexOf('#settings') > 1) {
      $("#dominion_blog_blogs").hide();
      $("#dominion_blog_css").hide();
      $("#dominion_blog_settings").show("fast");
  }  
  
</script>

<?php
  outPutCKEditorCode();
}

function outPutCKEditorCode(){
  global $SITEURL;
			if (defined('GSEDITORHEIGHT')) { $EDHEIGHT = GSEDITORHEIGHT .'px'; } else {	$EDHEIGHT = '500px'; }
			if (defined('GSEDITORLANG')) { $EDLANG = GSEDITORLANG; } else {	$EDLANG = 'en'; }
			if (defined('GSEDITORTOOL')) { $EDTOOL = GSEDITORTOOL; } else {	$EDTOOL = 'basic'; }
			if (defined('GSEDITOROPTIONS') && trim(GSEDITOROPTIONS)!="") { $EDOPTIONS = ", ".GSEDITOROPTIONS; } else {	$EDOPTIONS = ''; }
			
			if ($EDTOOL == 'advanced') {
				$toolbar = "
						['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Table', 'TextColor', 'BGColor', 'Link', 'Unlink', 'Image', 'RemoveFormat', 'Source'],
	          '/',
	          ['Styles','Format','Font','FontSize']
	      ";
			} elseif ($EDTOOL == 'basic') {
				$toolbar = "['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Link', 'Unlink', 'Image', 'RemoveFormat', 'Source']";
			} else {
				$toolbar = GSEDITORTOOL;
			}
?>
<script type="text/javascript" src="../admin/template/js/ckeditor/ckeditor.js"></script>

			<script type="text/javascript">

			var editor = CKEDITOR.replace( 'blog_contents', {
	        skin : 'getsimple',
	        forcePasteAsPlainText : true,
	        language : '<?php echo $EDLANG; ?>',
	        defaultLanguage : '<?php echo $EDLANG; ?>',
	        entities : true,
	        uiColor : '#f1e4be',
			height: '350',
			baseHref : '<?php echo $SITEURL; ?>',
	        toolbar : 
	        [
	        <?php echo $toolbar; ?>
			]
			<?php echo $EDOPTIONS; ?>
	        //filebrowserBrowseUrl : '/browser/browse.php',
	        //filebrowserImageBrowseUrl : '/browser/browse.php?type=Images',
	        //filebrowserWindowWidth : '640',
	        //filebrowserWindowHeight : '480'
    		});
            var editorExcerpts = CKEDITOR.replace( 'blog_short', {
	        skin : 'getsimple',
	        forcePasteAsPlainText : true,
	        language : '<?php echo $EDLANG; ?>',
	        defaultLanguage : '<?php echo $EDLANG; ?>',
	        entities : true,
	        uiColor : '#f1e4be',
			height: '150',
			baseHref : '<?php echo $SITEURL; ?>',
	        toolbar : 
	        [
	        <?php echo $toolbar; ?>
			]
			<?php echo $EDOPTIONS; ?>
	        //filebrowserBrowseUrl : '/browser/browse.php',
	        //filebrowserImageBrowseUrl : '/browser/browse.php?type=Images',
	        //filebrowserWindowWidth : '640',
	        //filebrowserWindowHeight : '480'
    		});            

			</script>
<script type="text/javascript">
	//<![CDATA[

// Added GS image files to be selectable via the image insert system.
// Author : Dominion IT
// url : www.dominion-it.co.za
//Version : 0.4
//GS version : 2.03
//date last changed  : 27 Sep 2010 21:42
CKEDITOR.on( 'dialogDefinition', function( ev )
	{
		var dialogName = ev.data.name;
		var dialogDefinition = ev.data.definition;
		
        if ( dialogName == 'image' ) {
			var infoTab = dialogDefinition.getContents( 'info' );
            var dlg = dialogDefinition.dialog;
 
			//Add the combo box
            infoTab.add( {
                    id : 'cmbGSImages',
                    type : 'select',
                    labelLayout : 'horizontal',
                    widths : [ '35%','85%' ],
                    style : 'width:90px',
                    label : 'Uploaded Images :',
                    'default' : '',
                    items :
                    [   
                        [ 'Custom' , 'CUSTOM']
    
                       <?php
                            $path = GSDATAUPLOADPATH;
                            $filenames = getFiles($path); 
                            if (count($filenames) != 0) {
                                foreach ($filenames as $file) {
                                    if (!(($file == "." || $file == ".." || is_dir($path . $file) || $file == ".htaccess"))) {
                                       $ext = substr($file, strrpos($file, '.') + 1);
                                        if (strtolower($ext) == 'gif' || strtolower($ext) == 'jpg' || strtolower($ext) == 'jpeg' || strtolower($ext) == 'png') {
                                            $URLtothefile = $SITEURL."data/uploads/$file";
                                            echo ",[ '$file' , '$URLtothefile']";
                                        }
                                    }
                                }
                            }
                       ?>
                        
                    ],
                    onChange : function() {
                      var cmbValue = this.getValue();
                      if (cmbValue != 'CUSTOM') {
                        dlg.setValueOf( 'info', 'txtUrl', cmbValue );
                        this.setValue('CUSTOM');
                      }  
                    }

				});
                infoTab.add( {
                    id : 'cmbGSImagesThumbs',
                    type : 'select',
                    labelLayout : 'horizontal',
                    widths : [ '35%','85%' ],
                    style : 'width:90px',
                    label : 'Thumbnails of Images : ',
                    'default' : '',
                    items :
                    [   
                        [ 'Custom' , 'CUSTOM']
    
                       <?php
                            $path = GSTHUMBNAILPATH;
                            $filenames = getFiles($path); 
                            if (count($filenames) != 0) {
                                foreach ($filenames as $file) {
                                    if (!(($file == "." || $file == ".." || is_dir($path . $file) || $file == ".htaccess"))) {
                                        $ext = substr($file, strrpos($file, '.') + 1);
                                        if (strtolower($ext) == 'gif' || strtolower($ext) == 'jpg' || strtolower($ext) == 'jpeg' || strtolower($ext) == 'png') {
                                             if (strpos($file,'thumbnail.') !== FALSE) {
                                              $URLtothefile = $SITEURL."data/thumbs/$file";
                                              echo ",[ '$file' , '$URLtothefile']";
                                             }  
                                        }
                                    }
                                }
                            }
                       ?>
                        
                    ],
                    onChange : function() {
                      var cmbValue = this.getValue();
                      if (cmbValue != 'CUSTOM') {
                        dlg.setValueOf( 'info', 'txtUrl', cmbValue );
                        this.setValue('CUSTOM');
                      }  
                    }

				}); 
                infoTab.add( {
                    id : 'cmbGSSmallImages',
                    type : 'select',
                    labelLayout : 'horizontal',
                    widths : [ '35%','85%' ],
                    style : 'width:90px',
                    label : 'Small version of Images : ',
                    'default' : '',
                    items :
                    [   
                        [ 'Custom' , 'CUSTOM']
    
                       <?php
                            $path = GSTHUMBNAILPATH;
                            $filenames = getFiles($path); 
                            if (count($filenames) != 0) {
                                foreach ($filenames as $file) {
                                    if (!(($file == "." || $file == ".." || is_dir($path . $file) || $file == ".htaccess"))) {
                                        $ext = substr($file, strrpos($file, '.') + 1);
                                        if (strtolower($ext) == 'gif' || strtolower($ext) == 'jpg' || strtolower($ext) == 'jpeg' || strtolower($ext) == 'png') {
                                             if (strpos($file,'thumbsm.') !== FALSE) {
                                              $URLtothefile = $SITEURL."data/thumbs/$file";
                                              echo ",[ '$file' , '$URLtothefile']";
                                             }  
                                        }
                                    }
                                }
                            }
                       ?>
                        
                    ],
                    onChange : function() {
                      var cmbValue = this.getValue();
                      if (cmbValue != 'CUSTOM') {
                        dlg.setValueOf( 'info', 'txtUrl', cmbValue );
                        this.setValue('CUSTOM');
                      }  
                    }

				}); 
 
		}  
        if ( dialogName == 'link' ) {
			var linkTab = dialogDefinition.getContents( 'info' );
            var linkdlg = dialogDefinition.dialog;
 
			//Add the combo box
            linkTab.add( {
                    id : 'cmbGSFiles',
                    type : 'select',
                    labelLayout : 'horizontal',
                    widths : [ '35%','85%' ],
                    style : 'width:90px',
                    label : 'Uploaded Files ',
                    'default' : '',
                    items :
                    [   
                        [ 'Custom' , 'CUSTOM']
    
                       <?php
                            $path = GSDATAUPLOADPATH;
                            $filenames = getFiles($path); 
                            if (count($filenames) != 0) {
                                foreach ($filenames as $file) {
                                        if (!(($file == "." || $file == ".." || is_dir($path . $file) || $file == ".htaccess"))) {
                                                $URLtothefile = $SITEURL."data/uploads/$file";
                                                $URLtothefile = str_replace("http://","",$URLtothefile);
                                                echo ",[ '$file' , '$URLtothefile']";
                                        
                                        }
                                }
                            }
                       ?>
                        
                    ],
                    onChange : function() {
                      var cmbValue = this.getValue();
                      if (cmbValue != 'CUSTOM') {
                        linkdlg.setValueOf( 'info', 'url', cmbValue );
                        this.setValue('CUSTOM');
                        
                      }  
                    }

				});
		}         
	});
	//]]>
	</script>	

<?php
}

function dominion_blog_dump($targetBlog){
  global $SITEURL;
  global $dominion_blogs_data_path;
  global $dominion_blogs_blogs_path;
  global $dominion_blogs_group_file;
  
  if (is_file($dominion_blogs_data_path.$dominion_blogs_group_file)) {
              $xml = getDominionXML($dominion_blogs_data_path.$dominion_blogs_group_file);
  } else {
     return "<br/><b>PLEASE CONFIGURE BLOB SYSTEM FIRST</b>";
  }
  
  $targetPath = $SITEURL .'plugins/dominion-blog/blogs/';
  $dblog_id = isset($_GET['dblog_id'])?$_GET['dblog_id']:-1;
  $dblog_showall = isset($_GET['dblog_showall'])?$_GET['dblog_showall']:0;
  $dblog_newsitem = isset($_GET['dblog_newsitem'])?$_GET['dblog_newsitem']:0; 
  $dblog_newsitemid = isset($_GET['dblog_newsitemid'])?$_GET['dblog_newsitemid']:0; 
  $dnews_showall = isset($_GET['dnews_showall'])?$_GET['dnews_showall']:0; 
  
  
  if ($dblog_newsitem  == 0) {
    $activeItem = $xml->xpath("//id[@group_name=\"$targetBlog\"]");
  } else {
    $activeItem = $xml->xpath("//id[@group_name=\"$dblog_newsitem\"]");
  }  
  if (count($activeItem) <= 0) {
     return "<br/><b>PLEASE CONFIGURE BLOB SYSTEM FIRST</b>";
  }
  
  $tmpAttr = $activeItem[0]->attributes();
  $activeFile  = $tmpAttr['group_blog_file'];
  $xmlLeerTeiken = $activeFile.".xml";  
   if (is_file($dominion_blogs_blogs_path.$xmlLeerTeiken)) {
       $xmlBlogFile = getDominionXML($dominion_blogs_blogs_path.$xmlLeerTeiken);
  } else {
     return "<br/><b>PLEASE CONFIGURE BLOB SYSTEM FIRST</b>";
  }  
  $settingsArr = dominion_blog_getSettings();
  $news_items = isset($settingsArr[2])?$settingsArr[2]:5;
  $blog_items = isset($settingsArr[3])?$settingsArr[3]:5;
  $show_blog_summary = isset($settingsArr[6])?$settingsArr[6] :0;
  
  if ($dblog_newsitem  === 0) {
      if ($dblog_id  >= 1) {
        //k we have real block we got to work with here.
        $BlogData = $xmlBlogFile->xpath("//id[@blog_id=\"$dblog_id\"]");
        return dominion_blog_showBlogContents($BlogData);
      } else {
        //K we have to show a list (remember to add max list of not all to show)
        //$BlogData = $xmlBlogFile->xpath("//id");
        if ($dblog_showall == 0) {
          //have to change to configured amount in future.
          if ($show_blog_summary == 1) {
            return dominion_blog_showBlogs($xmlBlogFile,$blog_items);    
          } else {  
            return dominion_blog_showBlogsALL($xmlBlogFile,-1);
          }  
        } else {
          return dominion_blog_showBlogsALL($xmlBlogFile,-1);
        }  
      }
  } else {
       if ($dblog_newsitemid  >= 1) {
        //k we have real block we got to work with here.
        //$BlogData = $xmlBlogFile->xpath("//id[@blog_id=\"$dblog_newsitemid\"]");
        return dominion_blog_showNewsContents($xmlBlogFile,$dblog_newsitemid,$dblog_newsitem);
      } else {
        //K we have to show a list (remember to add max list of not all to show)
        if ($dnews_showall == 0) {
          //have to change to configured amount in future.
          return dominion_blog_showNewsItems($xmlBlogFile,$news_items,$dblog_newsitem);    
        } else {
          return dominion_blog_showNewsItemsAll($xmlBlogFile,-1,$dblog_newsitem);
        }  
      }  
  }  
}

function createDefaultGroupAndBlog($blog_name){
  //will return the new category ID
        global $dominion_blogs_group_file;
        global $dominion_blogs_data_path;
        global $dominion_blogs_blogs_path;
        $numGroups = 1;
        $newBlogID = 1;
        if (is_file($dominion_blogs_data_path.$dominion_blogs_group_file)) {
          $xml = getDominionXML($dominion_blogs_data_path.$dominion_blogs_group_file);
          $items = $xml->xpath("//id");
          $numGroups = count($items);
          $numGroups++;
          $validate = $xml->xpath("//id[@group_id=\"$numGroups\"]"); 
          if (count($validate)>0) {
            while (count($validate)>0) {
              $numGroups++;
              $validate = $xml->xpath("//id[@group_id=\"$numGroups\"]"); 
            }
          }
          $newBlogFile = "dominion-blogs-$numGroups";
          $newBlogID =  $numGroups;
          unset($items);
        } else {
          $xml = @new DominionSimpleXML('<?xml version="1.0" encoding="UTF-8"?><blogs></blogs>');
          $newBlogFile = 'dominion-blogs-1';
        }
        
        $script = $xml->addChild('id');
        $script->addAttribute('group_id', $newBlogID);
        $script->addAttribute('group_blog_file', $newBlogFile);
        $script->addAttribute('group_name', $blog_name);

        $xml->XMLSave($dominion_blogs_data_path . $dominion_blogs_group_file);
        unset($xml);

        $xml = @new DominionSimpleXML('<?xml version="1.0" encoding="UTF-8"?><blog></blog>');
        $script = $xml->addChild('id');
        
        $script->addAttribute('group_id', $newBlogID);
        $script->addAttribute('blog_id', '1');
        $blok = '<a href="http://www.dominion-it.co.za"><img src="http://www.dominion-it.co.za/etiket.png"></a> Example blog contents description';
		$script_info = $script->addChild('blog_contents');
		$script_info->addCData(@$blok);
        $blok = 'Blog Title';
		$script_info = $script->addChild('blog_title');
		$script_info->addCData(@$blok);
        $blok = 'Excerpt';
		$script_info = $script->addChild('blog_short');
		$script_info->addCData(@$blok);
        $blok = date('d-m-Y');
		$script_info = $script->addChild('blog_date');
		$script_info->addCData(@$blok);        
        $xml->XMLSave($dominion_blogs_blogs_path . $newBlogFile.".xml");
        unset($xml);
        return $newBlogID;
}

function loadBlogFileforGroup($activeGroupItem,&$blogFile,&$groupID){
        global $dominion_blogs_blogs_path;
        
        $atr = $activeGroupItem[0]->attributes();
        $blogFile =  $atr['group_blog_file']; 
        $groupID = $atr['group_id'];
        $xml = getDominionXML($dominion_blogs_blogs_path.$blogFile.".xml");
        return $xml;
}

function addNewBlog($activeGroupItem){
         global $dominion_blogs_blogs_path;
        $xml = loadBlogFileforGroup($activeGroupItem,$blogFile,$groupID); 
        $hoevBlogs = count($xml->xpath('//id'));
        $hoevBlogs++;
        $validate = $xml->xpath("//id[@blog_id=\"$hoevBlogs\"]"); 
        if (count($validate)>0) {
          while (count($validate)>0) {
            $hoevBlogs++;
            $validate = $xml->xpath("//id[@blog_id=\"$hoevBlogs\"]"); 
          }
          unset($validate);
        }        
        $script = $xml->addChild('id');
        
        $script->addAttribute('group_id', $groupID);
        $script->addAttribute('blog_id', $hoevBlogs);
        $blok = '<a href="http://www.dominion-it.co.za"><img src="http://www.dominion-it.co.za/etiket.png"></a> Example blog contents description';
		$script_info = $script->addChild('blog_contents');
		$script_info->addCData(@$blok);
        $blok = 'Blog Title';
		$script_info = $script->addChild('blog_title');
		$script_info->addCData(@$blok);
        $blok = 'Excerpt';
		$script_info = $script->addChild('blog_short');
		$script_info->addCData(@$blok);
        
        $blok = date('d-m-Y');
		$script_info = $script->addChild('blog_date');
		$script_info->addCData(@$blok);        
        $xml->XMLSave($dominion_blogs_blogs_path.$blogFile.".xml");
        saveIndexesForBlog($xml,$blogFile);
        unset($xml);
        return $hoevBlogs;
}

function deleteBlog($activeGroupItem,$DeleteBlogID){
         global $dominion_blogs_blogs_path;
        $xml = loadBlogFileforGroup($activeGroupItem,$blogFile,$groupID); 
        
        $theItem = $xml->xpath("//id[@blog_id=\"$DeleteBlogID\"]");
        if  ((count($theItem) > 0)) {
          $theItem[0]->removeCurrentChild();
          $xml->XMLSave($dominion_blogs_blogs_path.$blogFile.".xml");
          saveIndexesForBlog($xml,$blogFile);
        }  
}

function deleteGroupandBlogs($targeGroupID){
         global $dominion_blogs_group_file;
        global $dominion_blogs_data_path;
        global $dominion_blogs_blogs_path;
        if (is_file($dominion_blogs_data_path.$dominion_blogs_group_file)) {
          $xml = getDominionXML($dominion_blogs_data_path.$dominion_blogs_group_file);
          $items = $xml->xpath("//id[@group_id=\"$targeGroupID\"]");
          if (count($items) > 0) {
            $atr = $items[0]->attributes();
            $blogfile = $atr['group_blog_file'];
            unlink($dominion_blogs_blogs_path.$blogfile.".xml");
            $items[0]->removeCurrentChild();
            $xml->XMLSave($dominion_blogs_data_path.$dominion_blogs_group_file);
          }
          unset($items);
        }         
}

function getBlogXMLObject($activeGroupItem,&$blogFile){
      $xml = loadBlogFileforGroup($activeGroupItem,$blogFile,$groupID); 
      return $xml;
}


function getBlogForEditing($activeGroupItem,$blogID){
         //global $dominion_blogs_data_path;
        $xml = loadBlogFileforGroup($activeGroupItem,$blogFile,$groupID); 
    if ($blogID == -1) {
      $curBlogs = $xml->xpath("//id");
    } else {
      
      $curBlogs = $xml->xpath("//id[@blog_id=\"$blogID\"]");
    }    
    
    $numBlogs = count($curBlogs);   
    if ($numBlogs >= 0) {
      return $curBlogs[0];
    } else {
      return FALSE; //error.. what do we doo.. what do we doo !! ? :D
    }
}

function dominion_blog_header(){
  global $dominion_blogs_data_path;
  global $SITEURL;
  echo "<style type='text/css'>  //DOMINION BLOG CSS STYLESHEET \r\n";
  echo file_get_contents($dominion_blogs_data_path.'dominion_css.css');
  $imgCal = $SITEURL."plugins/dominion-blog/blogs/img/Calendar.png";
  echo "\r\n #dominion_blog_date{ text-align:center; background-image: url('$imgCal'); width:49px; height:49px;font-color : black; float:right;} ";

  echo "</style>";
}
function buildBlogsList($activeItem){
    global $dominion_blogs_data_path;
    global $dominion_blogs_data_path;
    global $SITEURL;
include getLanguageFile('dominion-blog',dominion_blog_getActiveLanguage());
   $xml = loadBlogFileforGroup($activeItem,$blogFile,$groupID); 
    $curBlogs = $xml->xpath("//id");
    $numBlogs = count($curBlogs);
    echo "<p><table><tr><th>".$dominion_blog_general['SYSTEM_ITEM_GRID_TITLE']."</th><th>".$dominion_blog_general['SYSTEM_ITEM_GRID_DATE']."</th></tr><th></th><tr>";
    for ($x=0;$x < $numBlogs;$x++) {
      $prodAttr = $curBlogs[$x]->attributes();
      $blog_id = $prodAttr['blog_id'];
      $groupID =  $prodAttr['group_id'];
      $title = stripslashes($curBlogs[$x]->blog_title);
      $blog_date = stripslashes($curBlogs[$x]->blog_date);
      $adminID = $_GET['id'];
      $pUrl = $SITEURL."admin/load.php?id=".$adminID."&blogedit=$blog_id&groupid=$groupID";
      $pDeleteUrl = $SITEURL."admin/load.php?id=".$adminID."&blogdelete=$blog_id&groupid=$groupID";
      if ($numBlogs == 1 ) {
        echo "<tr><td><a href='$pUrl'>$title</a></td><td>$blog_date</td></tr>";
      } else {
        echo "<tr><td><a href='$pUrl'>$title</a></td><td>$blog_date</td><td><a href='$pDeleteUrl'>".$dominion_blog_general['SYSTEM_ITEM_LINK_DELETE']."</a></td></tr>";
      }      
    }
    echo "</tr></table></p>";
}

function dominion_blog_showBlogsALL($xmlBlogFile,$rowCount){
getBaseSiteURLandAddChar($baseURL,$addChar); 
  include getLanguageFile('dominion-blog',dominion_blog_getActiveLanguage());
  $blogIndex = $xmlBlogFile->xpath("//dataindex");
  if (count($blogIndex) <= 0) {
     return "<br/><b>PLEASE INDEX BLOB SYSTEM FIRST (save again)</b>";
  }
  $dataIndex = explode(",",$blogIndex[0]->index);
  unset($blogIndex);
  $teller = count($dataIndex);
  $curNewsItem = (isset($_GET['curnbitemblok']))?$_GET['curnbitemblok']:-1;
  if ($curNewsItem < 0) {
    $curNewsItem = 0;
  }
  $pagePerShow = 6; //TODO : Make configurable
  $totPageBloks = ceil($teller/$pagePerShow);
  
  $prev = ($curNewsItem >0)?$curNewsItem - 1:-1;
  $next = ($curNewsItem < $totPageBloks-1 )?$curNewsItem + 1:-1;
  $PrevURL = $baseURL.$addChar."dblog_showall=1&curnbitemblok=$prev";
  $NextURL = $baseURL.$addChar."dblog_showall=1&curnbitemblok=$next";
  if ($prev == -1) {
      $PrevURL = '';
  } else {
     $PrevURL = "<a href='$PrevURL'><< </a>";
  }
  if ($next == -1) {
      $NextURL = '';
  } else {
     $NextURL = "<a href='$NextURL'> >></a>";
  }  
  $pageShowCounter =1;  
  $DataToOutPut = '';
  $DataToOutPut = "<div id='dominion_blog_target_blok' style='width:95%;'> ";
  for ($x = ($curNewsItem * $pagePerShow);$x < $teller; $x++ ) {
     $nextID = $dataIndex[$x];
     $blogData = $xmlBlogFile->xpath("//id[@blog_id=\"$nextID\"]");
     include getLanguageFile('dominion-blog',dominion_blog_getActiveLanguage());
     $atr = $blogData[0]->attributes();
     $blog_id = $atr['blog_id'];
     //$group_id= $atr['group_id'];
     $blog_short = stripslashes($blogData[0]->blog_short);
     $blog_title = stripslashes($blogData[0]->blog_title);
     $blog_date = stripslashes($blogData[0]->blog_date);
     $blog_dateM = $dominion_blog_months[date('n',strtotime($blog_date))];
     $blog_dateD = date('d',strtotime($blog_date));
     $blog_date = "<span style='text-align:center; font-size:0.7 em;color:white;   '>$blog_dateM</span><br/><span style='font-size:1.8em;'>$blog_dateD</span>";     
     
     $targetURL = $baseURL.$addChar."dblog_id=$blog_id";
     $DataToOutPut .= "<div><div id='dominion_blog_header' ><a href='$targetURL'>$blog_title</a></div><div id='dominion_blog_date'>$blog_date </div></div>";
     $DataToOutPut .= "<div id='dominion_blog_data' >$blog_short</div>";
     //TODO : Add if date must be shown via config
     $DataToOutPut .= "<div id='dominion_blog_footer'></div>";
     $pageShowCounter++;
     if ($pageShowCounter > $pagePerShow) {
      break;
     }     
  }
  $DataToOutPut .= "</div>";
  $DataToOutPut .= "<div>";
  $curNewsItemMooi = $curNewsItem+1;
  $DataToOutPut .= "<p style='text-align:center'> $PrevURL [$curNewsItemMooi  / $totPageBloks ] $NextURL </p>";
  $DataToOutPut .= "</div>";
  return $DataToOutPut;
}

function dominion_blog_showBlogs($xmlBlogFile,$rowCount){
  getBaseSiteURLandAddChar($baseURL,$addChar); 
  include getLanguageFile('dominion-blog',dominion_blog_getActiveLanguage());
  $blogIndex = $xmlBlogFile->xpath("//dataindex");
  if (count($blogIndex) <= 0) {
     return "<br/><b>PLEASE INDEX BLOB SYSTEM FIRST (save again)</b>";
  }
  $dataIndex = explode(",",$blogIndex[0]->index);
  unset($blogIndex);
  $teller = count($dataIndex);
  
  $DataToOutPut = '';
  $DataToOutPut = "<div id='dominion_blog_target_blok' style='width:95%;'> ";
  for ($x = 0;$x < $teller; $x++ ) {
     if (($rowCount >= 0) && ($x >= $rowCount)) {
       $showAllURL = $baseURL.$addChar."dblog_showall=1";
       $DataToOutPut .= "<div id='dominion_blog_footer' ><a href='$showAllURL'>".$dominion_blog_general['SYSTEM_ITEM_LINK_SHOW_ALL_BLOGS']."</a></div>";
       break;
     }
     
     $nextID = $dataIndex[$x];
     $blogData = $xmlBlogFile->xpath("//id[@blog_id=\"$nextID\"]");
     include getLanguageFile('dominion-blog',dominion_blog_getActiveLanguage());
     $atr = $blogData[0]->attributes();
     $blog_id = $atr['blog_id'];
     //$group_id= $atr['group_id'];
     $blog_short = stripslashes($blogData[0]->blog_short);
     $blog_title = stripslashes($blogData[0]->blog_title);
     $blog_date = stripslashes($blogData[0]->blog_date);
     $blog_dateM = $dominion_blog_months[date('n',strtotime($blog_date))];
     $blog_dateD = date('d',strtotime($blog_date));
     $blog_date = "<span style='text-align:center; font-size:0.7 em;color:white;   '>$blog_dateM</span><br/><span style='font-size:1.8em;'>$blog_dateD</span>";     
     
     $targetURL = $baseURL.$addChar."dblog_id=$blog_id";
     $DataToOutPut .= "<div><div id='dominion_blog_header' ><a href='$targetURL'>$blog_title</a></div><div id='dominion_blog_date'>$blog_date </div></div>";
     $DataToOutPut .= "<div id='dominion_blog_data' >$blog_short</div>";
     //TODO : Add if date must be shown via config
     $DataToOutPut .= "<div id='dominion_blog_footer'></div>";
  }
  $DataToOutPut .= "</div>";
  return $DataToOutPut;
}

function dominion_blog_showBlogContents($blogData){
     getBaseSiteURLandAddChar($baseURL,$addChar); 
     $DataToOutPut = "<div id='dominion_blog_target_blok' style='width:95%;'> ";
     $atr = $blogData[0]->attributes();
     $blog_id = $atr['blog_id'];
     //$group_id= $atr['group_id'];
     include getLanguageFile('dominion-blog',dominion_blog_getActiveLanguage());
     $blog_contents = stripslashes($blogData[0]->blog_contents);
     $blog_title = stripslashes($blogData[0]->blog_title);
     $blog_date = stripslashes($blogData[0]->blog_date);
     $blog_dateM = $dominion_blog_months[date('n',strtotime($blog_date))];
     $blog_dateD = date('d',strtotime($blog_date));
     $blog_date = "<span style='text-align:center; font-size:0.7 em;color:white;   '>$blog_dateM</span><br/><span style='font-size:1.8em;'>$blog_dateD</span>";     
     $targetURL = $baseURL;
     $DataToOutPut .= "<div><p style='float:left;font-size:0.7em;'><a href='$targetURL'><< Back to Blogs</a></p></div>"; 
     $DataToOutPut .= "<div><div id='dominion_blog_header' >$blog_title</div><div id='dominion_blog_date'>$blog_date </div></div>";
     $DataToOutPut .= "<div id='dominion_blog_data' >$blog_contents</div>";
     //TODO : Add if date must be shown via config
     $DataToOutPut .= "<div id='dominion_blog_footer'></div>";
     $DataToOutPut .= "</div>";
     return $DataToOutPut;
} 

function dominion_blog_showNewsItemsAll($xmlBlogFile,$rowCount,$newsItemGroup){
  getBaseSiteURLandAddChar($baseURL,$addChar); 
  include getLanguageFile('dominion-blog',dominion_blog_getActiveLanguage());
  $newsIndex = $xmlBlogFile->xpath("//dataindex");
  if (count($newsIndex) <= 0) {
     echo "<br/><b>PLEASE INDEX BLOB SYSTEM FIRST (save again)</b>";
     exit;  
  }

  $dataIndex = explode(",",$newsIndex[0]->index);
  unset($newsIndex);
  
  $curNewsItem = (isset($_GET['curnbitemblok']))?$_GET['curnbitemblok']:-1;
  if ($curNewsItem < 0) {
    $curNewsItem = 0;
  }
  $pagePerShow = 6; //TODO : Make configurable
  $teller = count($dataIndex);
  $totPageBloks = ceil($teller/$pagePerShow);
  
  $prev = ($curNewsItem >0)?$curNewsItem - 1:-1;
  $next = ($curNewsItem < $totPageBloks-1 )?$curNewsItem + 1:-1;
  $PrevURL = $baseURL.$addChar."dnews_showall=1&curnbitemblok=$prev&dblog_newsitem=".urlencode($newsItemGroup);
  $NextURL = $baseURL.$addChar."dnews_showall=1&curnbitemblok=$next&dblog_newsitem=".urlencode($newsItemGroup);
  if ($prev == -1) {
      $PrevURL = '';
  } else {
     $PrevURL = "<a href='$PrevURL'><< </a>";
  }
  if ($next == -1) {
      $NextURL = '';
  } else {
     $NextURL = "<a href='$NextURL'> >></a>";
  }  
  $pageShowCounter =1;
  $DataToOutPut = '';
  $DataToOutPut = "<div id='dominion_blog_target_blok' style='width:95%;'> ";
  for ($x = ($curNewsItem * $pagePerShow);$x < $teller; $x++ ) {
     $nextID = $dataIndex[$x];
     $newsData = $xmlBlogFile->xpath("//id[@blog_id=\"$nextID\"]");
     $atr = $newsData[0]->attributes();
     $blog_id = $atr['blog_id'];
     //$group_id= $atr['group_id'];
     $blog_short = stripslashes($newsData[0]->blog_short);
     $blog_title = stripslashes($newsData[0]->blog_title);
     $settingsArr = dominion_blog_getSettings();
     $show_news_short = isset($settingsArr[4]) && ($settingsArr[4] == 1)?1:0;
     //$blog_date = stripslashes($newsData[$x]->blog_date);
     if (isset($settingsArr[5]) && !empty($settingsArr[5]) && ($settingsArr[5] != "[no-target-page]")) {
       global $SITEURL;
       $targetURL = $SITEURL."index.php?id=".$settingsArr[5]."&dblog_newsitemid=$blog_id&dblog_newsitem=".urlencode($newsItemGroup);
     } else {   
       $targetURL = $baseURL.$addChar."dblog_newsitemid=$blog_id&dblog_newsitem=".urlencode($newsItemGroup);
     }  
     $DataToOutPut .= "<div id='dominion_news_list_header' style='padding-left:10px;'><a href='$targetURL'>$blog_title</a></div>";
     if ($show_news_short == 1) {
       $DataToOutPut .= "<div id='dominion_news_data' style='padding-left:13px;'>$blog_short</div>";
     }  
     $pageShowCounter++;
     if ($pageShowCounter > $pagePerShow) {
      break;
     }
     
     //TODO : Add if date must be shown via config
     //$DataToOutPut .= "<div id='dominion_news_footer'> </div>";
  }
  $DataToOutPut .= "</div>";
  $DataToOutPut .= "<div>";
  $curNewsItemMooi = $curNewsItem+1;
  $DataToOutPut .= "<p style='text-align:center'> $PrevURL [$curNewsItemMooi  / $totPageBloks ] $NextURL </p>";
  $DataToOutPut .= "</div>";
  return $DataToOutPut;
}

function dominion_blog_showNewsItems($xmlBlogFile,$rowCount,$newsItemGroup){
  getBaseSiteURLandAddChar($baseURL,$addChar); 
  include getLanguageFile('dominion-blog',dominion_blog_getActiveLanguage());
  $newsIndex = $xmlBlogFile->xpath("//dataindex");
  if (count($newsIndex) <= 0) {
     echo "<br/><b>PLEASE INDEX BLOB SYSTEM FIRST (save again)</b>";
     exit;  
  }

  $dataIndex = explode(",",$newsIndex[0]->index);
  unset($newsIndex);
  $teller = count($dataIndex);
  $DataToOutPut = '';
  $DataToOutPut = "<div id='dominion_blog_target_blok' style='width:95%;'> ";
  for ($x = 0;$x < $teller; $x++ ) {
     if (($rowCount >= 0) && ($x >= $rowCount)) {
       $showAllURL = $baseURL.$addChar."dnews_showall=1&dblog_newsitem=".urlencode($newsItemGroup);
       $DataToOutPut .= "<div id='dominion_news_footer' ><a href='$showAllURL'>".$dominion_blog_general['SYSTEM_ITEM_LINK_SHOW_ALL_NEWS']."</a></div>";
       break;
     }
     $nextID = $dataIndex[$x];
     $newsData = $xmlBlogFile->xpath("//id[@blog_id=\"$nextID\"]");
     $atr = $newsData[0]->attributes();
     $blog_id = $atr['blog_id'];
     //$group_id= $atr['group_id'];
     $blog_short = stripslashes($newsData[0]->blog_short);
     $blog_title = stripslashes($newsData[0]->blog_title);
     $settingsArr = dominion_blog_getSettings();
     $show_news_short = isset($settingsArr[4]) && ($settingsArr[4] == 1)?1:0;
     //$blog_date = stripslashes($newsData[$x]->blog_date);
     if (isset($settingsArr[5]) && !empty($settingsArr[5]) && ($settingsArr[5] != "[no-target-page]")) {
       global $SITEURL;
       $targetURL = $SITEURL."index.php?id=".$settingsArr[5]."&dblog_newsitemid=$blog_id&dblog_newsitem=".urlencode($newsItemGroup);
     } else {   
       $targetURL = $baseURL.$addChar."dblog_newsitemid=$blog_id&dblog_newsitem=".urlencode($newsItemGroup);
     }  
     $DataToOutPut .= "<div id='dominion_news_list_header' style='padding-left:10px;'><a href='$targetURL'>$blog_title</a></div>";
     if ($show_news_short == 1) {
       $DataToOutPut .= "<div id='dominion_news_data' style='padding-left:13px;'>$blog_short</div>";
     }  
     //TODO : Add if date must be shown via config
     //$DataToOutPut .= "<div id='dominion_news_footer'> </div>";
  }
  $DataToOutPut .= "</div>";
  return $DataToOutPut;
}    
function dominion_blog_showNewsContents(&$xmlBlogFile,$dblog_newsitemid,$newsItemGroup){
     getBaseSiteURLandAddChar($baseURL,$addChar); 
     $newsIndex = $xmlBlogFile->xpath("//dataindex");
     if (count($newsIndex) <= 0) {
        echo "<br/><b>PLEASE INDEX BLOB SYSTEM FIRST (save again)</b>";
        exit;  
     }
     $dataIndex = explode(",",$newsIndex[0]->index);
     unset($newsIndex);
     $inxKey = array_search($dblog_newsitemid,$dataIndex);
     if ($inxKey == 0) {
       $prev = -1;
     } else {
       $prev = $dataIndex[$inxKey - 1];
     }
     if ($inxKey == (count($dataIndex)-1)) {
       $next = -1;
     } else {
       $next = $dataIndex[$inxKey + 1];
     }
     $pos = $inxKey+1;
     $totNews = count($dataIndex);
     $PrevURL = $baseURL.$addChar."dblog_newsitemid=$prev&dblog_newsitem=".urlencode($newsItemGroup);
     $NextURL = $baseURL.$addChar."dblog_newsitemid=$next&dblog_newsitem=".urlencode($newsItemGroup);
     if ($prev == -1) {
       $PrevURL = '';
     } else {
       $PrevURL = "<a href='$PrevURL'><< </a>";
     }
     if ($next == -1) {
       $NextURL = '';
     } else {
       $NextURL = "<a href='$NextURL'> >></a>";
     }
     
     
     include getLanguageFile('dominion-blog',dominion_blog_getActiveLanguage());
     $newsData = $xmlBlogFile->xpath("//id[@blog_id=\"$dblog_newsitemid\"]");
     $DataToOutPut = "<div id='dominion_blog_target_blok' style='width:95%;'> ";
     $atr = $newsData[0]->attributes();
     $blog_id = $atr['blog_id'];
     //$group_id= $atr['group_id'];
     $blog_contents = stripslashes($newsData[0]->blog_contents);
     $blog_title = stripslashes($newsData[0]->blog_title);
     $blog_date = stripslashes($newsData[0]->blog_date);
     $blog_dateM = $dominion_blog_months[date('n',strtotime($blog_date))];
     $blog_dateD = date('d',strtotime($blog_date));
     $blog_date = "<span style='text-align:center; font-size:0.7 em;color:white;   '>$blog_dateM</span><br/><span style='font-size:1.8em;'>$blog_dateD</span>";
     $settingsArr = dominion_blog_getSettings();
     if (isset($settingsArr[5]) && !empty($settingsArr[5]) && ($settingsArr[5] != "[no-target-page]")) {
       global $SITEURL;
       $targetURL = $SITEURL."index.php?id=".$settingsArr[5]."&dnews_showall=1&dblog_newsitem=".urlencode($newsItemGroup);
     } else {
       $targetURL = $baseURL;
     }
     
     $DataToOutPut .= "<div><p style='float:left;font-size:0.7em;'><a href='$targetURL'><< Back</a></p><p style='text-align:right'> $PrevURL [ $pos / $totNews ] $NextURL </p></div>";
     $DataToOutPut .= "<div><div id='dominion_news_header' >$blog_title </div><div id='dominion_blog_date'>$blog_date </div></div>";
     $DataToOutPut .= "<div id='dominion_news_data' >$blog_contents</div>";
     //TODO : Add if date must be shown via config
     $DataToOutPut .= "<div id='dominion_news_footer'></div>";
     $DataToOutPut .= "</div>";
     return $DataToOutPut;
} 

function dominion_news_show($news_group){
       if (!isPluginEnabled('dominion-blog')) {
       echo '<p>News System offline</p>';
       exit;
     }
  global $dominion_blogs_data_path;
  global $dominion_blogs_blogs_path;
  global $dominion_blogs_group_file;
  
  if (is_file($dominion_blogs_data_path.$dominion_blogs_group_file)) {
              $xml = getDominionXML($dominion_blogs_data_path.$dominion_blogs_group_file);
  } else {
     echo "<br/><b>PLEASE CONFIGURE BLOB SYSTEM FIRST</b>";
     exit;
  }
  $activeItem = $xml->xpath("//id[@group_name=\"$news_group\"]");
  if (count($activeItem) <= 0) {
     echo "<br/><b>PLEASE CONFIGURE BLOB SYSTEM FIRST</b>";
     exit;
  }
  
  $tmpAttr = $activeItem[0]->attributes();
  $activeFile  = $tmpAttr['group_blog_file'];
  $xmlLeerTeiken = $activeFile.".xml";  
   if (is_file($dominion_blogs_blogs_path.$xmlLeerTeiken)) {
       $xmlBlogFile = getDominionXML($dominion_blogs_blogs_path.$xmlLeerTeiken);
  } else {
     echo "<br/><b>PLEASE CONFIGURE BLOB SYSTEM FIRST</b>";
     exit;
  }  
  $settingsArr = dominion_blog_getSettings();
  $news_items = isset($settingsArr[2])?$settingsArr[2]:5;
  
  echo dominion_blog_showNewsItems($xmlBlogFile,$news_items,$news_group);    
}


function dominion_blog_getActiveLanguage(){
  global $dominion_blogs_data_path;
  global $dominion_blogs_settings_file;
  $settingsStr = file_get_contents($dominion_blogs_data_path.$dominion_blogs_settings_file);
  $settingsArr = explode(',',$settingsStr);
  return $settingsArr[1];
}

function dominion_blog_getSettings(){
  global $dominion_blogs_data_path;
  global $dominion_blogs_settings_file;
  $settingsStr = file_get_contents($dominion_blogs_data_path.$dominion_blogs_settings_file);
  $settingsArr = explode(',',$settingsStr);
  return $settingsArr;
}

?>