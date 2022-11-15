<a class="sr-only focus:not-sr-only" href="#main">
  <?php echo e(__('Skip to content')); ?>

</a>

<?php echo $__env->make('sections.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<div class="grid grid-cols-8 gap-8 container mt-16 mb-10  ">
    <main id="main" class="main col-start-1 col-end-7  ">
      <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php if (! empty(trim($__env->yieldContent('sidebar')))): ?>
      <aside class="sidebar col-start-7 col-end-9  ">
        <?php echo $__env->yieldContent('sidebar'); ?>
      </aside>
    <?php endif; ?>

</div>
<?php echo $__env->make('sections.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH /var/www/blog-tas-es/wp-content/themes/blog-tas/resources/views/layouts/app.blade.php ENDPATH**/ ?>