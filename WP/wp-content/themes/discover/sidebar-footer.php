<div id="footer-bar1" class="four columns">
<?php if ( is_active_sidebar( 'first-footer-widget-area' ) ) { ?>
					<ul class="xoxo">
						<?php dynamic_sidebar( 'first-footer-widget-area' ); ?>
					</ul>
					
					<?php } else { ?>
			
			<ul class="xoxo">
			<li class="widget-container"><h3 class="widget-title"><?php _e( 'Meta', 'discover' ); ?></h3>	<ul>
			<li><?php _e( 'Site Admin', 'discover' ); ?></li>			<li><?php _e( 'Log out', 'discover' ); ?></li>
			<li><?php _e( 'Entries ', 'discover' ); ?><abbr title="Really Simple Syndication"><?php _e( 'RSS', 'discover' ); ?></abbr></li>
			<li><?php _e( 'Comments ', 'discover' ); ?><abbr title="Really Simple Syndication"><?php _e( 'RSS', 'discover' ); ?></abbr></li>
			<li><a href="http://wordpress.org/" title="Powered by WordPress, state-of-the-art semantic personal publishing platform."><?php _e( 'WordPress.org', 'discover' ); ?></a></li>
						</ul>	</li>	</ul>					
					
<?php } ?>

</div><!--footer 1 end-->



<div id="footer-bar2" class="four columns">
<?php if ( is_active_sidebar( 'second-footer-widget-area' ) ) { ?>
					<ul class="xoxo">
						<?php dynamic_sidebar( 'second-footer-widget-area' ); ?>
					</ul>
<?php } else { ?>
		<ul class="xoxo">
	<li class="widget-container"><h3 class="widget-title"><?php _e( 'About us', 'discover' ); ?></h3><div class="textwidget">
	<?php _e( 'This is a text widget. Put your own widget by going to appeareance widget area. Nullam posuere felis a lacus tempor eget dignissim arcu adipiscing. ', 'discover' ); ?></div>
		</li></ul>
				
<?php } ?>
</div><!--footer 2 end-->


<div id="footer-bar3" class="four columns">
<?php if ( is_active_sidebar( 'third-footer-widget-area' ) ) { ?>
					<ul class="xoxo">
						<?php dynamic_sidebar( 'third-footer-widget-area' ); ?>
					</ul>
					
	<?php } else { ?>
		<ul class="xoxo">
	<li class="widget-container"><h3 class="widget-title"><?php _e( 'Contact Us', 'discover' ); ?></h3><div class="textwidget"><?php _e( 'This is a text widget. Put your own widget by going to appeareance widget area. Nullam posuere felis a lacus tempor eget dignissim arcu adipiscing. ', 'discover' ); ?></div>
		</li></ul>
				
<?php } ?>
</div><!--footer 3 end-->