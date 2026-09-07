<?php

/**
 * Template Name: Trang Chi Tiết Dịch Vụ (Service Detail)
 * Description: Template hiển thị chi tiết dịch vụ Thiên Tâm với đầy đủ các section ACF.
 *
 * @package ThienTamData
 */

get_header();
?>

<main id="primary" class="site-main service-detail-page">
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
