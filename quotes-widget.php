<?php 

/**
 * Quotes Widget
 *
 * @author Mark Coppock <mark@coppock.com>
 * @copyright Copyright () 2012
 * @version v 0.15 2015-08-26 03:38:00 GMT
 */

add_action( 'widgets_init', 'extended_quotes_register_widget' );
function extended_quotes_register_widget() {
	register_widget( 'Quotes_Widget' );
}

class Quotes_Widget extends \WP_Widget {

    /**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'widget_quotes', // Base ID
			__( 'Quotes', 'extended_quotes' ), // Name
			array( 'description' => __( 'A list of Quotations', 'extended_quotes' ), ) // Args
		);
	}

    public function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title'], $instance, $this->id_base);
		echo $before_widget;
		
		echo '<div id="quotes_wrap">';
		echo '<h4>Random Quotations</h4>';

		// http://stackoverflow.com/questions/11601038/in-wordpress-how-do-i-display-a-single-random-post-of-a-custom-post-type-in-a-s
		$args = array('post_type' => 'quote', 'posts_per_page' => '1', 'orderby' => 'rand');
		$quotations = new WP_Query( $args );

		while ( $quotations->have_posts()) : $quotations->the_post();

			global $post;

			$quotesource = get_post_meta( $post->ID, '_quote_source', true);
			//$quoteauthor = term_clean($post->ID, 'quoteauthor');
			$quoteauthors = get_the_terms( $post->ID, 'quoteauthor' );

			if ( $quoteauthors && ! is_wp_error( $quoteauthors ) ) :

				$authorlist = array();

				foreach ( $quoteauthors as $quoteauthor ) {
					$authorlist[] = $quoteauthor->name;
				}
				$extractedauthor = join(", ", $authorlist );
			endif;

			?>
			<h5 style="font-size:15px; font-weight:bold; color:#555; font-style:italic;"><?php the_title(); ?></h5>
			<?php the_content(); ?>
			<cite style="text-align:right;margin:-10px 1em 0 0;display:block;"> 
			<?php 
			
			//echo get_the_terms( $post->ID, 'quoteauthor' );
			echo $extractedauthor;

			if ( $quotesource ) echo ' <a href="' . $quotesource . '">(link)</a></cite>'; 	
		endwhile;
		wp_reset_postdata();
		echo '</div>';
		echo $after_widget;

		// // from http://wordpress.stackexchange.com/questions/23606/how-do-i-list-custom-taxonomy-terms-without-the-links
		// function term_clean($postid, $term) {
		// 	$terms = get_the_terms($postid, $term); 
	 //    	foreach ($terms as $term) {  echo $term->name;   };
		// }
	}
}
