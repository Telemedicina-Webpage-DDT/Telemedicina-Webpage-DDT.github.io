<?php
// File Security Check
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'You do not have sufficient permissions to access this page!' );
}
?>
 <link rel="stylesheet" href="NS/css/bootstrap.min.css" type="text/css">

    <!-- Custom Fonts -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css" type="text/css">

    <!-- Plugin CSS -->
    <link rel="stylesheet" href="NS/css/animate.min.css" type="text/css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="NS/css/creative.css" type="text/css">
<?php
/**
 * Template Name: Full Width
 *
 * This template is a full-width version of the page.php template file. It removes the sidebar area.
 *
 * @package WooFramework
 * @subpackage Template
 */
	get_header();
	global $woo_options;
?>
       
    <div id="content" class="page col-full">
    
    	<?php woo_main_before(); ?>
    	
		<section id="main" class="fullwidth">
           
        <?php
        	if ( have_posts() ) { $count = 0;
        		while ( have_posts() ) { the_post(); $count++;
        ?>                                                             
                <article <?php post_class(); ?>>
					 
					<header>
						<h1><?//php the_title(); ?></h1>
					</header>
                    
                    <section class="entry">
	                	<?php the_content(); ?>
	               	</section><!-- /.entry -->

					<?php edit_post_link( __( '{ Editar }', 'woothemes' ), '<span class="small">', '</span>' ); ?>

                </article><!-- /.post -->
                                                    
			<?php
					} // End WHILE Loop
				} else {
			?>
				<article <?php post_class(); ?>>
                	<p><?php _e( 'No hubo coincidencias :(  ', 'woothemes' ); ?></p>
                </article><!-- /.post -->
            <?php } ?>  
        
		</section><!-- /#main -->
		
		<?php //woo_main_after(); ?>
		
    </div><!-- /#content -->
		
<?php get_footer(); ?>