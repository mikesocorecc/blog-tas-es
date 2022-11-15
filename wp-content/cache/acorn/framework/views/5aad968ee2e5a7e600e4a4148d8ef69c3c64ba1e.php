<form role="search" method="get" class="rounded-full bg-[#f4f4f4] flex justify-around" action="<?php echo e(home_url('/')); ?>">
  <label class="w-3/4">
    <span class="sr-only">
      <?php echo e(_x('Search for:', 'label', 'sage')); ?>

    </span>

    <input
      type="search"
      class=" bg-[#f4f4f4] rounded-full p-2 focus-visible:outline-none w-full"
      placeholder="<?php echo esc_attr_x('Haz una búsqueda aquí', 'placeholder', 'sage'); ?>"
      value="<?php echo e(get_search_query()); ?>"
      name="s"
    >
  </label> 
  <button class=""><i class="fa-solid fa-magnifying-glass"></i></button>
</form>
<?php /**PATH /var/www/blog-tas-es/wp-content/themes/blog-tas/resources/views/forms/search.blade.php ENDPATH**/ ?>