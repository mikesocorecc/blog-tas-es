<?php $__env->startSection('content'); ?> 
  
      <?php while(have_posts()): ?> <?php (the_post()); ?>
      <?php echo $__env->first(['partials.content-' . get_post_type(), 'partials.content'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php endwhile; ?>  
  
  <?php echo get_the_posts_navigation(); ?>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('sidebar'); ?>
  <?php echo $__env->make('sections.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/blog-tas-es/wp-content/themes/blog-tas/resources/views/index.blade.php ENDPATH**/ ?>