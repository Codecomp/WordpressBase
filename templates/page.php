<?php get_header(); ?>

    <main role="main" class="<?php echo get_post_type(); ?>-single">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>

                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>

            <?php endwhile; ?>
        <?php endif; ?>
    </main>

<?php get_footer(); ?>
