<article <?php (post_class()); ?>>
  <header>
    <h2 class="entry-title text-primary text-2xl font-bold">
      <a href="<?php echo e(get_permalink()); ?>">
        <?php echo $title; ?>

      </a>
    </h2>
    <?php echo $__env->make('partials.entry-meta', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo e(the_content( __( 'Leer más <span class="bg-red-500">&rarr;</span>', 'twentyten' ) )); ?>



  </header>
  <div class="entry-summary">
    
  </div>
</article>
<?php /**PATH /var/www/blog-tas-es/wp-content/themes/blog-tas/resources/views/partials/content.blade.php ENDPATH**/ ?>