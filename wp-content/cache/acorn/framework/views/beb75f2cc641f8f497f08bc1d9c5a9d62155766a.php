<?php $__env->startSection('content'); ?>  
<div class="container my-11">
    <h2 class="font- text-5xl">
        ¡Descarga tu contenido predilecto <br>
        <span class="font-bold">cuando lo necesites! </span>
    </h2> 
</div>
<section class="bg-blue  ">
    <div class="container grid  grid-cols-3 pb-20">
        <div class="col-span-1 ml-1">
            <p class=" pt-8 text-white text-xl font-semibold">Infografía</p>
            <h3 class="text-white  text-4xl font-bold mt-5 mb-20">ALQUILERES EN ESPAÑA <br>
                PARA EXTRANJEROS</h3>
            <button class="bg-white rounded-2xl px-20 py-4 text-dark font-bold text-4xl">DESCARGAR</button>
        </div>
        <div class="col-span-2 flex justify-center">
            <img class=" pt-10" src="<?= \Roots\asset('images/banner-infografía.png'); ?>" alt="" srcset="">
        </div>
    </div>
</section>
<div class="my-11">
    <div class="container">
        <div class="flex">
            <h4 class="text-3xl font-bold">Categorías</h4>
            <div class="ml-8 flex items-center space-x-3">
                <div class=" flex"> 
                    <div class="flex items-center    ">
                        <input id="default-checkbox" type="checkbox" value="" class="w-5 h-5 text-blue-600 bg-gris-100-100 rounded-none border-black focus:ring-blue-500   ">
                        <label for="default-checkbox" class="ml-2 text-base font-bold text-black ">Guías</label>
                    </div> 
                </div>
                <div class=" flex"> 
                    <div class="flex items-center    ">
                        <input id="default-checkbox" type="checkbox" value="" class="w-5 h-5 text-blue-600 bg-gris-100-100 rounded-none border-black focus:ring-blue-500   ">
                        <label for="default-checkbox" class="ml-2 text-base font-bold text-black ">Infografía</label>
                    </div> 
                </div>
                <div class=" flex"> 
                    <div class="flex items-center    ">
                        <input id="default-checkbox" type="checkbox" value="" class="w-5 h-5 text-blue-600 bg-gris-100-100 rounded-none border-black focus:ring-blue-500   ">
                        <label for="default-checkbox" class="ml-2 text-base font-bold text-black ">Webinnar</label>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div> 
  
      <?php while(have_posts()): ?> <?php (the_post()); ?>
         <?php echo $__env->first(['partials.content-' . get_post_type(), 'partials.content'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php endwhile; ?>  
  
  <?php echo get_the_posts_navigation(); ?>


<?php $__env->stopSection(); ?>

 

<?php echo $__env->make('layouts.layout-full', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/blog-tas-es/wp-content/themes/blog-tas/resources/views/archive-recursos.blade.php ENDPATH**/ ?>