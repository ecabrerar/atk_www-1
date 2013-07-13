<?php
/*
Plugin Name: Breadcrumbs
Description: Set Breadcrubs for you theme
Version: 1.0.2
Author: webmasterdubai
Author URI: http://www.webmasterdubai.com/
 * thanks for @Oleg06 giving good idea to get page tile from xml file
 * good for multi language support
 * Thanks to @AustinTheSaint for nice snipest 
*/

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");


$bread_folder_name = 'breadcrumbs';
$bread_folder_name_folder_path = GSPLUGINPATH.$bread_folder_name.'/';
$rel_bread_folder_path = substr(GSPLUGINPATH, strlen(GSROOTPATH)).$bread_folder_name.'/';

//gallery settings file
$breadcrumbs_settings_file= $bread_folder_name_folder_path.'breadcrumbs_settings.xml';

# register plugin
register_plugin(
	$thisfile, 
	'Simple BreadCrumbs', 	
	'1.0.2',
	'Webmasterdubai',
	'http://www.webmasterdubai.com/', 
	'Simple BreadCrumbs works with Fancy URL',
	'plugins',
        'breadcrumbs_setup'
	
);

add_action('plugins-sidebar','createSideMenu',array($thisfile,'Simple BreadCrumbs'));


function breadcrumbs_setup(){

    global $breadcrumbs_settings_file;
    
    if($_POST['title'] && $_POST['separator']){
        $xml = @new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><item></item>');

        $note = $xml->addChild('breadcrumb_title');
        $note->addCData($_POST['title']);
        $note = $xml->addChild('breadcrumb_separator');
        $note->addCData($_POST['separator']);

        $xml->asXML($breadcrumbs_settings_file);

        breadcrumbs_msg_box('Breadcrumbs settings Saved Successfully!');

    }
    elseif(!$_POST['title'] && $_POST)
        breadcrumbs_msg_box('<strong>Error:</strong> You cannot save an empty title.',true);
    elseif(!$_POST['separator'] && $_POST)
        breadcrumbs_msg_box('<strong>Error:</strong> You cannot save an empty separator.',true);
    $setting_value = get_bradcrumbs_settings();
    
?>
<label>Simple Breadcrumbs settings</label> <br/><br/>
<p>Set title for home in you native language and also set your desired separator common separator are <strong>| > -</strong>.</p>
<form method="post" action="<?php echo $_SERVER ['REQUEST_URI']?>">
<table class="highlight" id="breadcrumbsTable">
    <tr>
        <td>BreadCrumb Home title</td>
        <td><input type="text" name="title" value="<?=$setting_value['title']?>" /> </td>
    </tr>
    <tr>
        <td>BreadCrumb separator</td>
        <td><input type="text" name="separator" value="<?=$setting_value['separator']?>" /> </td>
    </tr>
</table>
    <input type="submit" name="submit" class="submit" value="Save Settings" />
</form>
<?
}

function get_breadcrumbs() {
    global $SITEURL;
    $uri = trim($_SERVER["REQUEST_URI"],'/');
    $url_array =explode('/',$uri);
    $pageURL = $SITEURL;
    
    $code_dir = explode("/",ltrim($_SERVER["PHP_SELF"],'/'));
    
    $settings_value = get_bradcrumbs_settings();
    
    $crumbs = "<li><a href='$pageURL'>".$settings_value['title']."</a></li></li>";
    foreach($url_array as $url) {


        if( $code_dir['0'] != $url) {
            $data = getXML(GSDATAPAGESPATH . $url.".xml");
            $pageURL .= $data->url.'/';
			$crumbs .= '<li> '.$settings_value['separator'].' </li>';
            $crumbs .= "<li><a href='$pageURL'>".ucfirst($data->title)."</a></li>";
			
        }
      
    }

    echo $crumbs;

}

/**
 * Returns an array with Breadcrumbs title and separator
 *
 * @return array
 */
function get_bradcrumbs_settings() {
    global $breadcrumbs_settings_file;

    $breadcrumbs_settings = array();
    if (file_exists($breadcrumbs_settings_file)) {;
        $v = getXML($breadcrumbs_settings_file);
        $breadcrumbs_settings['title'] = $v->breadcrumb_title;
        $breadcrumbs_settings['separator'] = $v->breadcrumb_separator;
    }

    return $breadcrumbs_settings;
}

/**
 *  Generates a message box
 *  - Prepare it so that it can be used across all square it plugins
 */
if (!function_exists('breadcrumbs_msg_box'))
{
	function breadcrumbs_msg_box($msg,$error = false)
	{
            if($error)
                echo '<div class="error">'.$msg.'</div>';
            else
		echo '<div class="updated" >
				'.$msg.'
			 </div>';
	}
}
