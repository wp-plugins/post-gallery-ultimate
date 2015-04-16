	
	
 <?php get_header(); ?> 


<style>
	
</style>
<div class="pg-row" id="pg-headline">
	<h1><?php echo $headline; ?></h1>
</div>
<div class="pg-row" id="pg-header">
	<div class="pg-header-column">
		<?php echo do_shortcode($header); ?>
	</div>
</div>
<?php
	
	$pg_singlePostCount = 0;
	foreach($listOfPost as $singlePost){
		//echo $singlePost->post_name." <br>";
		$pg_galleryPost = $postGroup[$pg_singlePostCount];
		pg_make_post_display($singlePost, $pg_singlePostCount, $pg_galleryPost);
		//echo 'count: '.$pg_singlePostCount;
		$pg_singlePostCount++;
	}
	if(!($pg_singlePostCount % 3 == 2)){
		echo '</div>';
	}
	
	function pg_make_post_display($singlePost, $pg_PostCount, $pg_galleryPost){
		
		//global $pg_singlePostCount;
		
		if($pg_PostCount % 3 == 0 || $pg_PostCount == 0){
			echo '<div class="pg-row">';
			
		}
		
		echo '<div class="pg-columns">';
		if(isset($pg_galleryPost['title']) && trim($pg_galleryPost['title']) != ''){
			$pg_post_title = $pg_galleryPost['title'];
			//echo '<h2><a href="'.get_site_url().$singlePost->post_name.'">'.$pg_galleryPost['title'].'</a></h2>';
		}
		else{
			$pg_post_title = $singlePost->post_title;
		}
		echo '<h2><a href="'.get_site_url().'/'.$singlePost->post_name.'">'.$pg_post_title.'</a></h2>';
		//print_r($pg_galleryPost);
		if($pg_galleryPost['description_type'] == 'excerpt'){
			echo '<p>'.$singlePost->post_excerpt.'</p>';
		}
		elseif($pg_galleryPost['description_type'] == 'custom'){
			echo '<p>'.$pg_galleryPost['description'].'</p>';
		}
		echo '</div>';
		if($pg_PostCount % 3 == 2){
			echo '</div>';
		}
	}
?>

<div class="row" id="pg-footer">
	<div class="pg-footer-column">
		<?php echo do_shortcode($footer); ?>
	</div>
</div>

<?php get_footer(); ?>


</body>
</html>



