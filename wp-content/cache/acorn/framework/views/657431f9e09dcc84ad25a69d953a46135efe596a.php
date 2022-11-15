<div class="flex my-2 text-sm">
  <div class="mr-2 [&_a]:text-dark [&_a]:font-semibold">
    <?php echo get_the_category_list( ', ' ); ?>

  </div>
  <time class="updated" datetime="<?php echo e(get_post_time('c', true)); ?>">
    <span class="a">Publicado el <?php echo e(get_the_date('d/m/Y')); ?> por <?php echo e(get_the_author()); ?> </span>
  </time> 
</div>

<?php /**PATH /var/www/blog-tas-es/wp-content/themes/blog-tas/resources/views/partials/entry-meta.blade.php ENDPATH**/ ?>