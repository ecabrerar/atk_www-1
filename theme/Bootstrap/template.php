<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); }
/****************************************************
*
* @File: 		template.php
* @Package:		GetSimple
* @Action:		Todo
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
        <meta name="google-site-verification" content="RUyRO9QPJ96p35X7Dr_jgGTOkwUx6_8IyJj0AsX-FEQ" />
	<link rel="stylesheet" type="text/css" href="<?php get_theme_url(); ?>/style.css" media="all" />

	<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

       <link href="<?php get_theme_url(); ?>/prettify/prettify.css" type="text/css" rel="stylesheet" />
        <script type="text/javascript" src="<?php get_theme_url(); ?>/prettify/prettify.js"></script>

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
		min-height: 100%;
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
                 
                <?php if(return_page_slug() == 'index') { ?>
		table td {
		  vertical-align: top;
		  border-top: 1px solid #fff;
		}
		table tbody th {
		  border-top: 1px solid #fff;
		  vertical-align: top;
		}
                <?php } ?>

                .search-box {
                  margin-top: 7px;
                }

                .mybreadcrumb ul {
                   list-style-type: none;
                   background-image: url(navi_bg.png);
                   height: 80px;
                   width: 663px;
                   margin: auto;
                }

                .mybreadcrumb li {
                   display: inline;
                   text-shadow: 0 1px 0 #ffffff;
                }

                .search-blog .text{
                    width: 120px;
                }

                .nm_post_title a, a:hover{
                  color: gray;
                }

		.nm_post {
			margin-bottom: 60px;
		}

                .nm_post_title {

		  margin-bottom: 17px;
		  border-bottom: 1px solid #ddd;
		  -webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
		  -moz-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
		  box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);

                }

                .nm_post_date{
                  color: darkgray;
                }

                .nm_post_meta {
                    font-size: 10px;
                }
	</style>

        <script>
           var domainroot="www.astivetoolkit.org";
	   function Gsitesearch(curobj){
	     curobj.q.value="site:"+domainroot+" "+curobj.prependedInput.value
	   }
        </script>

	<script type="text/javascript">

	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-28086624-1']);
	  _gaq.push(['_setDomainName', 'astivetoolkit.org']);
	  _gaq.push(['_trackPageview']);

	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();

	</script>
</head>
<body id="<?php get_page_slug(); ?>" onload="prettyPrint()">
		
	<div class="topbar">
		<div class="fill">
			<div class="container">
                                <a class="brand" href="<?php get_site_url();?>">ATK</a>
				<ul class="nav">
					<?php get_navigation(return_page_slug()); ?>
				</ul>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="content">
            	    <div class="page-header page-header-top">
                        <div class="row">
                            <div class="span12">
			        <?php
				   if(return_page_slug() == 'index') {
			    		echo '<h1>Astive Toolkit <small>Write incredible Apps for Asterisk® PBX</small> </h1>';
				   } if(return_page_slug() == 'downloads') {
                                        echo '<h1>Downloads <small>Release Date: July 4th, 2013</small> </h1>';                                
                                   } if(return_page_slug() == 'documentation') {
                                        echo '<h1>Documentation <small>Everything you need to know about Astive</small> </h1>';                                
                                   } if(return_page_slug() == 'tutorials') {
                                         echo '<h1>Tutorials <small>The first steps needed to get your application up and running</small> </h1>';                                
                                   } if(return_page_slug() == 'tools') {
                                         echo '<h1>Tools <small>A set of helpfull tools</small> </h1>';                                
                                   } if(return_page_slug() == 'community') {
                                         echo '<h1>Community <small>Get in contact with other Asterisk developers</small> </h1>';                                
                                   } if(return_page_slug() == 'blog') {
                                         echo '<h1>Blog <small>News</small> </h1>';                                
                                   } else {
					$title = '<h1>'.return_page_title().'</h1>';
				  }
			         ?>
                             </div>
                             <div class="span4 search-box">
			         <form class="span4" action="http://www.google.com/search" method="get" onSubmit="Gsitesearch(this)">
				     <input name="q" type="hidden" />
                                     <div class="input-prepend">
                                       <span class="add-on">Google</span>
                                       <input onFocus="this.value='';" onBlur="this.value='Search...';" class="medium" value="Search..." id="prependedInput" name="prependedInput" type="text">
                                     </div>
			  	 </form>
                             </div>
                        </div>
                       
                        <div class="mybreadcrumb"><?php if(return_page_slug() != 'index') { get_breadcrumbs();} ?></div>
                    </div>
                    <div class="row">
                    <?php if(return_page_slug() == 'tools' || return_page_slug() == 'downloads' || return_page_slug() == 'documentation' || return_page_slug() == 'tutorials') { get_component('sidebar');} ?>                    
        	    <?php if(return_page_slug() != 'blog') {get_page_content();} ?>
                    <?php if(return_page_slug() == 'blog') { ?>
                       <div class="span12">
                           <?php get_page_content(); ?>
                       </div>
                       <?php get_component('blog-sidebar');?>
                    <?php } ?>
                    </div>

		</div>	
               <?php include('footer.php'); ?>
	</div>        
</body>
</html>