
<div class=" ">
    <?php echo get_search_form(false); ?>


    
    <div class="rounded-3xl bg-dark w-full h-full mt-8 px-8 py-12">
        <h3 class="text-cyan text-center font-bold  text-3xl">Suscríbete <br> a la biblioteca</h3>
        <p class="text-white mt-5 text-center">
            La mejor fuente de información
            con útiles consejos y guías.
            <b>¡Suscríbete!</b>
        </p>
        <form action="" class="mt-4">
            <input type="text" class="bg-white rounded-3xl w-full text-center p-2 focus-visible:outline-none"
                placeholder="Introduzca su Email">
            <button class="bg-info rounded-3xl w-full  p-3 mt-4 text-white font-semibold">Suscribirse</button>
            <label for="suscripcio_extra" class="mt-6 flex items-start">
                <input type="checkbox" class="mr-2 mt-2" name="" id="suscripcio_extra">
                <small class="text-white"> También deseo recibir correos electrónicos de productos y servicios de TAS
                    Consultoría. (Puede cancelar la suscripción cuando desee)</small>
            </label>
        </form>
    </div>

    
    <div class="mt-7">
        <div id="accordion-collapse" data-accordion="collapse">
            <h3><button type="button" id="accordion-collapse-heading-1"
                    class="flex items-center justify-between w-full py-2  text-left text-gray-500     rounded-t-xl  focus:ring-gray-200      "
                    data-accordion-target="#accordion-collapse-body-1" aria-expanded="true"
                    aria-controls="accordion-collapse-body-1">
                    <span class="font-bold text-2xl">Categorías</span>
                    <svg data-accordion-icon class="w-6 h-6 rotate-180 shrink-0" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd">
                        </path>
                    </svg>
                </button>
            </h3>
            <ul id="accordion-collapse-body-1" class="hidden mt-4 space-y-3"
                aria-labelledby="accordion-collapse-heading-1">
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="<?php echo e($category['class']); ?>">
                        <a href="<?php echo e($category['link']); ?>"><?php echo e($category['name']); ?></a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </div>

    
    <div class="mt-7">
        <h3 class="font-bold text-2xl">Post más populares</h3>
        <ul class="mt-4 space-y-4">
            <li class="flex space-x-3">
                <span
                    class="bg-primary text-white rounded-full  h-8 w-16 flex items-center justify-center font-bold">1</span>
                <div class="">
                    <h4 class="font-bold">Lorem ipsum</h4>
                    <p class="">
                        Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam dolor sit amet.
                    </p>
                </div>
            </li>
            <li class="flex space-x-3">
                <span
                    class="bg-primary text-white rounded-full  h-8 w-16 flex items-center justify-center font-bold">2</span>
                <div class="">
                    <h4 class="font-bold">Lorem ipsum</h4>
                    <p class="">
                        Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam dolor sit amet.
                    </p>
                </div>
            </li>
        </ul>
    </div>
    
    <div class="mt-7 h-full relative rounded-3xl text-center pb-8">
        <img  class="w-full absolute -z-10" src="<?= \Roots\asset('images/Asesores.png'); ?>" alt="">
        <h3 class="text-cyan font-bold text-2xl pt-48 ">Contacta</h3>
        <p class="text-white">con nuestro equipo <br>
            de asesores. </p>
        <p class="text-white mt-2">
            Pide tu presupuesto
        </p>
        <button class="bg-info rounded-2xl text-3xl    py-2  px-16 mt-4 text-white font-semibold">¡gratis!</button>
    </div>

     
    <div class=" sticky top-[5px]  ">  
        <div class="flex flex-col "> 
            <div class="  mt-16 flex justify-center  rounded-3xl text-center pb-8">
                <img class="h-72 w-52" src="<?= \Roots\asset('images/guia-empresa-espania.png'); ?>" alt="">
            </div> 
            <h3 class="text-info font-bold text-center text-2xl   ">¡Descarga gratis</h3>
            <p class="text-dark text-center">la Guía Práctica del Emprendedor en España! </p>
            <div class="text-center">
                <button class="bg-info rounded-2xl text-3xl py-2  px-10 mt-4 text-white font-semibold">¡AQUÍ!</button>
            </div>
        </div>
    </div>

</div>
<?php /**PATH /var/www/blog-tas-es/wp-content/themes/blog-tas/resources/views/sections/sidebar.blade.php ENDPATH**/ ?>