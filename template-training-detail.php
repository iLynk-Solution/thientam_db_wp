<?php

/**
 * Template Name: Trang Chi Tiết Đào Tạo (Training Detail)
 * Description: Template hiển thị chi tiết gói đào tạo & khóa học Tử Vi - Cổ Học Thiên Tâm với đầy đủ các section ACF.
 *
 * @package HelloElementorChild
 */

get_header();
?>

<main id="primary" class="site-main training-detail-page">
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
