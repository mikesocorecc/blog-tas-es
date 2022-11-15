<a class="sr-only focus:not-sr-only" href="#main">
  <?php echo e(__('Skip to content')); ?>

</a>

<?php echo $__env->make('sections.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> 
    <main id="main" class="  ">
      <?php echo $__env->yieldContent('content'); ?>
    </main>  
<?php echo $__env->make('sections.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH /var/www/blog-tas-es/wp-content/themes/blog-tas/resources/views/layouts/layout-full.blade.php ENDPATH**/ ?>