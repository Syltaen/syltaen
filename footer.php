<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package syltaen
 */
?>


	<footer role="contentinfo">

		<div class="container">
			<!-- 
			<div id="menu-footer">
				<?php wp_nav_menu( array( "menu" => "Menu footer" ) ); ?>
			</div>
			-->
			
			<p class="copy">
				&copy; XXXXXXX | Website by <a href="http://www.hungryminds.be" target="_blank">Hungry Minds</a>
			</p>

		</div>
		
	</footer><!-- #colophon -->

<!--
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'XXXXXXX', 'auto');
  ga('send', 'pageview');
</script>
-->

<?php 
	wp_footer(); 
?>

	</body>
</html>
