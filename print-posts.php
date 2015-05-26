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
//BB Dev: accessing wp global variables in order to use them in a plugin
global $wp_query;
global $wpdb;

$cat=$_GET["cat"];		// BB Dev: getting categry ID from URL
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
if($cat){
    // BB Develop: Get all posts with given special post type
	$sql = "
		SELECT wposts.* 
		FROM $wpdb->posts wposts
		WHERE wposts.post_title not like '%Вечерний урок Зоар%'
		AND wposts.post_title not like '%Утренний урок%'
		AND wposts.post_title not like '%Детский урок%'
		AND wposts.post_title not like '%Вы думали, каббала - это сложно%'
		AND wposts.post_title not like '%Беседа о социальной сети%'
		AND wposts.post_title not like '%Урок по письму Рабаша%'
		AND wposts.post_title not like '%Урок по Талмуду Десяти Сфирот%'
		AND wposts.post_title not like '%Урок по письму Бааль Сулама%'
		AND wposts.post_title not like '%Урок Зоар по недельной главе%'
		AND wposts.post_title not like '%Урок по статьям Бааль Сулама%'		
		AND wposts.post_status = 'publish' 
		AND wposts.post_type = 'post' 
		ORDER BY wposts.post_date DESC
	";

	$posts_in_category = $wpdb->get_results($sql); 
	
	$post_ids = array(); //BB Dev: creating array of post IDs for WP query
	foreach ($posts_in_category as $one_post){
		array_push($post_ids, $one_post->ID);
	}

	  
	
	$temp = $wp_query;
	$wp_query= null;
	$args = array (
	'cat' => $cat,
	'post__in' => $post_ids,
	'posts_per_page' => -1,
	'order' => 'DESC'
	);

	$wp_query = new WP_Query($args); //BB Dev: creating new WP loop for outputing posts we just selected using manual params
}
?>

<p style="text-align: center;"><strong>- <?php bloginfo('name'); ?> - <span dir="ltr"><?php bloginfo('url')?></span> -</strong></p>
<?php if($cat): // BB Dev -->?> 
	 <p style="text-align: center;"><strong>- <span dir="ltr"><?php echo(get_the_category_by_ID($cat)) ?></span> -</strong></p>
<?php endif; // BB Dev <--?>
<div class="Center">
	<div id="Outline">
		<?php if ($wp_query->have_posts()): ?>
			<?php while ($wp_query->have_posts()): $wp_query->the_post(); ?>
					<?php if($cat): //BB Dev: if category - print post title with link ?>
						 <p id="BlogTitle"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></p>
					<?php else: ?>
					<p id="BlogTitle"><?php the_title(); ?></p>
					<?php endif; //BB Dev?>
					<p id="BlogDate"><?php _e('Posted By', 'wp-print'); ?> <u><?php the_author(); ?></u> <?php _e('On', 'wp-print'); ?> <?php the_time(sprintf(__('%s @ %s', 'wp-print'), get_option('date_format'), get_option('time_format'))); ?> <?php _e('In', 'wp-print'); ?> <?php print_categories('<u>', '</u>'); ?> | <u><a href='#comments_controls'><?php print_comments_number(); ?></a></u></p>
					<div id="BlogContent"><?php print_content(); ?></div>
			<?php endwhile; ?>
			<hr class="Divider" style="text-align: center;" />
			<?php if(print_can('comments')): ?>
				<?php comments_template(); ?>
			<?php endif; ?>
			<p><?php _e('Article printed from', 'wp-print'); ?> <?php bloginfo('name'); ?>: <strong dir="ltr"><?php bloginfo('url'); ?></strong></p>
			<?php if($cat): //BB Dev -->?>
				<p><?php _e('URL to category', 'wp-print'); ?>: <strong dir="ltr"><?php echo (get_category_link($cat)); ?></strong></p>			
			<?php else: ?>
				<p><?php _e('URL to article', 'wp-print'); ?>: <strong dir="ltr"><?php the_permalink(); ?></strong></p>
			<?php endif; // BB Dev <--?>
			<?php if(print_can('links')): ?>
				<p><?php print_links(); ?></p>
			<?php endif; ?>
			<p style="text-align: <?php echo ('rtl' == $text_direction) ? 'left' : 'right'; ?>;" id="print-link"><?php _e('Click', 'wp-print'); ?> <a href="#Print" onclick="window.print(); return false;" title="<?php _e('Click here to print.', 'wp-print'); ?>"><?php _e('here', 'wp-print'); ?></a> <?php _e('to print.', 'wp-print'); ?></p>
		<?php else: ?>
				<p><?php _e('No posts matched your criteria.', 'wp-print'); ?></p>
		<?php endif; $wp_query = $temp; //BB Dev: restoring original WP query?>
	</div>
</div>
<p style="text-align: center;"><?php echo stripslashes($print_options['disclaimer']); ?></p>
</body>
</html>
