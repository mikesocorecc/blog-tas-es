<header class="banner ">
    <div class="  container text-right mt-2 ">
      <a class="px-4 border border-primary rounded-xl inline-block" href="http://www.tas-consultoria.com/">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Volver a tas-consultoria.com</span>
      </a>
    </div>
    <!-- Barra de navegacion -->
    <div class="container  flex justify-between mt-10">
        <!-- Logo -->
        <a href="<?php echo e(home_url('/')); ?>" class=" ">
            <img class="object-contain h-28" src="<?= \Roots\asset('images/logo.svg'); ?>">
        </a>
        <!-- Telefono -->
        <div class="flex items-center">
            <a class=" text-primary font-bold text-4xl" href="tel:+34937377525"> +34 937 3775 25</a>
        </div>
        
        <div class="flex justify-between items-center">
            <a href="http://www.tas-consultoria.com/blog-en" role="button">
                <img width="37" height="37" class="mx-2 " src="<?= \Roots\asset('images/icons/united-states-flag-icon.svg'); ?>" alt="">
            </a>
            <a class=" " href="http://www.tas-consultoria.com/blog-fr" role="button">
                <img width="37" height="37" class="mx-2 " src="<?= \Roots\asset('images/icons/france-flag-icon.svg'); ?>" alt="">
            </a>
            <a class=" " href="http://www.tas-consultoria.com/blog-cn" role="button">
                <img width="37" height="37" class="mx-2 " src="<?= \Roots\asset('images/icons/china-flag-icon.svg'); ?>" alt="">
            </a>
        </div>
    </div>

    <nav class="container mx-auto">
        <!-- Menu movil -->
        <button class=" " type="button">
            <span class="  "></span>
        </button>
        <!-- Listado menu -->
        <div class="container" id=" ">
            <ul class="flex justify-between">
                <?php
                // if (get_post_type() == 'recursos') {
                ?>
                    
                <?php
                // }
                ?>
                <?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class=" ">
                        <a class="text-primary text-xl font-bold" aria-current="page" href="<?php echo e($menu['url']); ?>">
                            <?php echo e($menu['menu']); ?>

                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </nav>
</header>
<?php /**PATH /var/www/blog-tas-es/wp-content/themes/blog-tas/resources/views/sections/header.blade.php ENDPATH**/ ?>