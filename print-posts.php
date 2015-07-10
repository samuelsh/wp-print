<?php
/*
 * WordPress Plugin: WP-Print
 * Copyright (c) 2012 Lester "GaMerZ" Chan
 *
 * File Written By:
 * - Lester "GaMerZ" Chan
 * - http://lesterchan.net
 *
 * File Information:
 * - Printer Friendly Post/Page Template
 * - wp-content/plugins/wp-print/print-posts.php
*/

// including PHPWord library
require_once plugin_dir_path(__FILE__).'/PHPWord/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();

//BB Dev: accessing wp global variables in order to use them in a plugin
global $wp_query;
global $wpdb;

$cat=isset($_GET["cat"]) ? $_GET['cat']:"";		// BB Dev: getting categry ID from URL
?>

<?php global $text_direction; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<title><?php bloginfo('name'); ?> <?php wp_title(); ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="Robots" content="noindex, nofollow" />
	<?php if(@file_exists(get_stylesheet_directory().'/print-css.css')): ?>
		<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/print-css.css" type="text/css" media="screen, print" />
	<?php else: ?>
		<link rel="stylesheet" href="<?php echo plugins_url('wp-print/print-css.css'); ?>" type="text/css" media="screen, print" />
	<?php endif; ?>
	<?php if('rtl' == $text_direction): ?>
		<?php if(@file_exists(get_stylesheet_directory().'/print-css-rtl.css')): ?>
			<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/print-css-rtl.css" type="text/css" media="screen, print" />
		<?php else: ?>
			<link rel="stylesheet" href="<?php echo plugins_url('wp-print/print-css-rtl.css'); ?>" type="text/css" media="screen, print" />
		<?php endif; ?>
	<?php endif; ?>
	<link rel="canonical" href="<?php the_permalink(); ?>" />
</head>
<body>

<?php // ----------------------------------BB Dev---------------------------------------------------------------------
if($cat): 
    // BB Dev: Get all posts with given special post type
	$offset = 0;
	$excluded_posts = explode(",", $print_options['exclude_posts']);
	$excluded_posts = implode("|", $excluded_posts);
	$postsInCat = get_term_by('name',get_cat_name($cat),'category'); // getting # of posts in category
	$posts_num = $postsInCat->count;
	$cat_name = get_cat_name($cat);
	$phpWord_allowed_tags = array('p' => array(), 'h1' => array(), 'h2' => array(), 'h3' => array(),
			 'h4' => array(), 'h5' => array(), 'h6' => array(), 'strong' => array(),
			 'em' => array(), 'sup' => array(),'sub' => array(), 'table' => array(), 'tr' => array(),
			 'td' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(), 'textarea' => array());
	echo $posts_num;
	
	/*$sql = "
		SELECT * 
		FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		LEFT JOIN $wpdb->terms ON($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
		WHERE post_status = 'publish'
		AND $wpdb->terms.name = '".$cat_name."'
		AND $wpdb->term_taxonomy.taxonomy = 'category'
		AND post_type = 'post' 
 		AND post_title not regexp '".$excluded_posts."'
		ORDER BY post_date DESC LIMIT 100 OFFSET $offset
		";*/

	$sql = $wpdb->prepare("SELECT * 
		FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		LEFT JOIN $wpdb->terms ON($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
		WHERE post_status = 'publish'
		AND $wpdb->terms.name = '%s'
		AND $wpdb->term_taxonomy.taxonomy = 'category'
		AND post_type = 'post' 
 		AND post_title not regexp '%s'
		ORDER BY post_date DESC LIMIT 100 OFFSET %d", $cat_name, $excluded_posts, $offset);
	
	$posts_in_category = $wpdb->get_results($sql, OBJECT);
	
	
	if($print_options['write_to_file']) {
        // Creating the new document...
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
		
        $section = $phpWord->addSection();
  
        $section->addText(htmlspecialchars('- '.get_bloginfo('name').' - '.get_bloginfo('url')), 
        		array('size' => 11, 'bold' => true, 'name' => 'Veranda'), array('align' => 'center'));
        
        // Add hyperlink elements
        /*$section->addLink(
        		get_bloginfo('url'),
        		htmlspecialchars(get_bloginfo('url')),
        		array('color' => '0000FF', 'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE,'pStyle')
        );*/
        
        
        $section->addText(htmlspecialchars(get_the_category_by_ID($cat)), 
        		array('size' => 14, 'bold' => true, 'name' => 'Veranda'), array('align' => 'center'));
        $section->addTextBreak(2);
        
		while($posts_in_category) {

			if($posts_in_category) {
				global $post;
				
				foreach ($posts_in_category as $post) {
					
					setup_postdata($post);
					
					$section->addLink(get_the_permalink(),htmlspecialchars(get_the_title()),
							array('size' => 14, 'bold' => true, 'name' => 'Veranda'), array('align' => 'left')  );
					
					$textrun = $section->addTextRun(array('align' => 'left'));
					$textrun->addText(htmlspecialchars("Posted by "),array('size' => 12, 'bold' => false, 'name' => 'Veranda'));
					$textrun->addText(htmlspecialchars(get_the_author()), array('size' => 12, 'italic' => true, 'name' => 'Veranda'));
					$textrun->addText(htmlspecialchars(" On ".get_the_time(sprintf(__('%s @ %s', 'wp-print'), get_option('date_format'), 
						get_option('time_format')))." In ".get_the_category_by_ID($cat)." | ". print_comments_number(false)),
							array('size' => 12, 'bold' => false, 'name' => 'Veranda'), 
							array('align' => 'left'));
					$section->addTextBreak(1, null, array('borderBottomSize' => 1,
							'borderColor' => '000000'));
						
					//$section->addText(htmlspecialchars(print_content(false)),array('size' => 14, 'bold' => false, 'name' => 'Veranda'));
					\PhpOffice\PhpWord\Shared\Html::addHtml($section, balanceTags(wp_kses(print_content(false), 
							$phpWord_allowed_tags), true));
					$section->addTextBreak(2);
				}
			}
			$offset += 100;
			$sql = $wpdb->prepare("SELECT *
					FROM $wpdb->posts
					LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
					LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
					LEFT JOIN $wpdb->terms ON($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
					WHERE post_status = 'publish'
					AND $wpdb->terms.name = '%s'
					AND $wpdb->term_taxonomy.taxonomy = 'category'
					AND post_type = 'post'
					AND post_title not regexp '%s'
					ORDER BY post_date DESC LIMIT 100 OFFSET %d", $cat_name, $excluded_posts, $offset);
			$posts_in_category = $wpdb->get_results($sql, OBJECT);
		}	
        
		$phpWord->save(wp_upload_dir()['basedir'].'/test.docx', 'Word2007');
	}
	$offset = 0;
	$sql = $wpdb->prepare("SELECT * 
		FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		LEFT JOIN $wpdb->terms ON($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
		WHERE post_status = 'publish'
		AND $wpdb->terms.name = '%s'
		AND $wpdb->term_taxonomy.taxonomy = 'category'
		AND post_type = 'post' 
 		AND post_title not regexp '%s'
		ORDER BY post_date DESC LIMIT 100 OFFSET %d", $cat_name, $excluded_posts, $offset);
	
	$posts_in_category = $wpdb->get_results($sql, OBJECT);
	
	?>

<main role="main" class="center">

			<span class="hat">
				<strong>
					- <?php bloginfo('name'); ?> 
					- 
					<span dir="ltr"><?php bloginfo('url')?></span> 
					-
				</strong>
<p style="text-align: center;"><strong> - <span dir="ltr"><?php echo(get_the_category_by_ID($cat)) ?></span> -</strong></p>
			</span>
<?php while ($posts_in_category): ?>
		<?php if ($posts_in_category): ?>
			<?php global $post; ?>
			<?php foreach ($posts_in_category as $post): ?>
				<?php setup_postdata($post); ?>
			
			<header class="entry-header">
				<h1 class="entry-title">
					<?php the_title(); ?>
				</h1>

				<span class="entry-date">

				<?php _e('Posted By', 'wp-print'); ?> 

				<cite><?php the_author(); ?></cite> 

				<?php _e('On', 'wp-print'); ?> 

				<time>	
					<?php the_time(sprintf(__('%s @ %s', 'wp-print'), 
						get_option('date_format'), 
						get_option('time_format'))); 
					?> 
				</time>

			  	<span>
			  		<?php _e('In', 'wp-print'); ?> 
			  		<?php print_categories(); ?> | 
			  	</span>	

		  		<a href='#comments_controls'>
		  			<?php print_comments_number(); ?>
	  			</a>	  			

				</span>
			
			</header>	

			<div class="entry-content">

				<?php print_content(); ?>

			</div>
						
			<?php endforeach; ?>
		<?php else: ?>
				<p><?php _e('No posts matched your criteria.', 'wp-print'); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php $offset += 100; 

	/*$sql = "
		SELECT * 
		FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		LEFT JOIN $wpdb->terms ON($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
		WHERE post_status = 'publish'
		AND $wpdb->terms.name = '".$cat_name."'
		AND $wpdb->term_taxonomy.taxonomy = 'category'
		AND post_type = 'post' 
 		AND post_title not regexp '".$excluded_posts."'
		ORDER BY post_date DESC LIMIT 100 OFFSET $offset
		";*/
	
	$sql = $wpdb->prepare("SELECT * 
		FROM $wpdb->posts
		LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
		LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
		LEFT JOIN $wpdb->terms ON($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
		WHERE post_status = 'publish'
		AND $wpdb->terms.name = '%s'
		AND $wpdb->term_taxonomy.taxonomy = 'category'
		AND post_type = 'post' 
 		AND post_title not regexp '%s'
		ORDER BY post_date DESC LIMIT 100 OFFSET %d", $cat_name, $excluded_posts, $offset);
	$posts_in_category = $wpdb->get_results($sql, OBJECT);
?>
<?php endwhile;?>
<footer class="footer">
<p><?php _e('Article printed from', 'wp-print'); ?> <?php bloginfo('name'); ?>: <strong dir="ltr"><?php bloginfo('url'); ?></strong></p>
<p><?php _e('URL to category', 'wp-print'); ?>: <strong dir="ltr"><?php echo (get_category_link($cat)); ?></strong></p>			
<p style="text-align: <?php echo ('rtl' == $text_direction) ? 'left' : 'right'; ?>;" id="print-link">
        <a href="#Print" onclick="window.print(); return false;" title="<?php _e('Click here to print.', 'wp-print'); ?>">
                <?php _e('Click', 'wp-print'); ?> 
                <?php _e('here', 'wp-print'); ?>
                <?php _e('to print.', 'wp-print'); ?>
        </a> 
</p>
<p style="text-align: center;"><?php echo stripslashes($print_options['disclaimer']); ?></p>
</footer>
</body>
</html>

<?php else: ?>
<main role="main" class="center">

	<?php if (have_posts()): ?>

		<header class="entry-header">

			<span class="hat">
				<strong>
					- <?php bloginfo('name'); ?> 
					- 
					<span dir="ltr"><?php bloginfo('url')?></span> 
					-
				</strong>
			</span>
			
			<?php while (have_posts()): the_post(); ?>

			<h1 class="entry-title">
				<?php the_title(); ?>
			</h1>

			<span class="entry-date">

				<?php _e('Posted By', 'wp-print'); ?> 

				<cite><?php the_author(); ?></cite> 

				<?php _e('On', 'wp-print'); ?> 

				<time>	
					<?php the_time(sprintf(__('%s @ %s', 'wp-print'), 
						get_option('date_format'), 
						get_option('time_format'))); 
					?> 
				</time>

			  	<span>
			  		<?php _e('In', 'wp-print'); ?> 
			  		<?php print_categories(); ?> | 
			  	</span>	

		  		<a href='#comments_controls'>
		  			<?php print_comments_number(); ?>
	  			</a>	  			

				</span>
			
		</header>	

		<div class="entry-content">

			<?php print_content(); ?>

		</div>

	<?php endwhile; ?>
	
	<div class="comments">
		<?php if(print_can('comments')): ?>
			<?php comments_template(); ?>
		<?php endif; ?>
	</div>
	
	<footer class="footer">
		<p>
			<?php _e('Article printed from', 'wp-print'); ?> 
			<?php bloginfo('name'); ?>: 

			<strong dir="ltr">
				<?php bloginfo('url'); ?>
			</strong>
		</p>

		<p>
			<?php _e('URL to article', 'wp-print'); ?>: 
			<strong dir="ltr">
				<?php the_permalink(); ?>
			</strong>
		</p>
		
		<?php if(print_can('links')): ?>
			<p><?php print_links(); ?></p>
		<?php endif; ?>

		<p style="text-align: <?php echo ('rtl' == $text_direction) ? 'left' : 'right'; ?>;" id="print-link">
			<a href="#Print" onclick="window.print(); return false;" title="<?php _e('Click here to print.', 'wp-print'); ?>">
				<?php _e('Click', 'wp-print'); ?> 
				<?php _e('here', 'wp-print'); ?>
				<?php _e('to print.', 'wp-print'); ?>
			</a> 
		</p>

		<?php else: ?>
			<p>
				<?php _e('No posts matched your criteria.', 'wp-print'); ?>
			</p>
		<?php endif; ?>

		<p style="text-align: center;">
			<?php echo stripslashes($print_options['disclaimer']); ?>
		</p>
	</footer>

</main>
</body>
</html>
<?php endif; ?>