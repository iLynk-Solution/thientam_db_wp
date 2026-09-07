<?php

/**
 * Single Template for Training Post Type (Chi tiết gói đào tạo)
 *
 * @package HelloElementorChild
 */

get_header();
?>

<main id="primary" class="site-main single-training-page">
    <?php
    while (have_posts()) :
        the_post();
    ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</main>

<?php
get_footer();
