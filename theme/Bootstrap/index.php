<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); }
/****************************************************
*
* @File: 			template.php
* @Package:		GetSimple
* @Action:		Cardinal theme for the GetSimple CMS
*
*****************************************************/
?>
<!DOCTYPE html>
<html>
<head>

	<!-- Site Title -->
	<title><?php get_page_clean_title(); ?> &lt; <?php get_site_name(); ?></title>
	<?php get_header(); ?>
	<meta name="robots" content="index, follow" />
	<link rel="stylesheet" type="text/css" href="<?php get_theme_url(); ?>/style.css" media="all" />

	<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- Le styles -->
	<link href="bootstrap.css" rel="stylesheet">
	<style type="text/css">
		/* Override some defaults */
		html, body {
		background-color: #eee;
		}
		body {
		padding-top: 40px; /* 40px to make the container go all the way to the bottom of the topbar */
		}

		.container > footer p {
		text-align: center; /* center align it with the container */
		}
		/* .container {
		width: 820px; downsize our container to make the content feel a bit tighter and more cohesive. NOTE: this removes two full columns from the grid, meaning you only go to 14 columns and not 16. 
		}*/

		/* The white background content wrapper */
		.content {
		background-color: #fff;
		padding: 20px;
		margin: 0 -20px; /* negative indent the amount of the padding to maintain the grid system */
		-webkit-border-radius: 0 0 6px 6px;
		   -moz-border-radius: 0 0 6px 6px;
			border-radius: 0 0 6px 6px;
		-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.15);
		   -moz-box-shadow: 0 1px 2px rgba(0,0,0,.15);
			box-shadow: 0 1px 2px rgba(0,0,0,.15);
		}

		/* Page header tweaks */
		.page-header-top {
		background-color: #f5f5f5;
		padding: 20px 20px 10px;
		margin: -20px -20px 20px;
		}

		/* Styles you shouldn't keep as they are for displaying this base example only */
		.content .span10,
		.content .span4 {	
		min-height: 500px;
		}
		/* Give a quick and non-cross-browser friendly divider */
		.content .span4 {
		margin-left: 0;
		padding-left: 19px;
		/* border-left: 1px solid #eee;*/
		}

		.topbar .btn {
		border: 0;
		}

		table td {
		  vertical-align: top;
		  border-top: 1px solid #fff;
		}
		table tbody th {
		  border-top: 1px solid #fff;
		  vertical-align: top;
		}

	</style>

</head>
<body id="<?php get_page_slug(); ?>" >
		
	<div class="topbar">
		<div class="fill">
			<div class="container">
				<a class="brand" href="#">Astive</a>
				<ul class="nav">
					<?php get_navigation(return_page_slug()); ?>
				</ul>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="content">

				<?php get_page_content(); ?>

		</div>	

               <?php include('footer.php'); ?>
	</div>		

        
</body>
</html>
