<div class="max-w-5xl mx-auto my-11">
    <div class="grid grid-cols-2 gap-x-8 gap-y-16">
            <div class=" bg-gris-100 rounded-lg"> 
                 <?php echo get_the_post_thumbnail( get_the_ID(), 'full', array( 'class' => 'w-full' ) ); ?>

                <div class="px-8 pb-8 pt-4">
                    <h5 class="text-dark text-xl">Infografía</h5>
                    <h4 class="font-bold text-dark text-2xl leading-6 mb-4"><a href="<?php echo e(get_permalink()); ?>"> <?php echo $title; ?> </a></h4>
                    <p class="text-xl"><?php echo e(the_excerpt()); ?></p>
                </div>
            </div>  
    </div>
</div><?php /**PATH /var/www/blog-tas-es/wp-content/themes/blog-tas/resources/views/partials/content-recursos.blade.php ENDPATH**/ ?>