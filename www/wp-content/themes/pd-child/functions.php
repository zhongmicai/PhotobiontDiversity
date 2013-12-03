add_action( 'init', 'mytheme_setup' );
add_theme_support( 'post-thumbnails' );
 
function mytheme_setup() {
set_post_thumbnail_size( 100, 100, true );
add_image_size( 'single-post-thumbnail', 250, 250 );
}