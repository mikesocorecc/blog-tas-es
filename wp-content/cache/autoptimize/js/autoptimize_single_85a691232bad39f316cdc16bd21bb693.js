jQuery(function($){$("#filtrar .filtro__lista .filtro__item").on('click',function(){const enlace=$(this).val()
if(this.checked){$('#lista-guias li').not($(`.${enlace}`)).fadeOut()
$(`.${enlace}`).fadeIn();$('.filtro__item').not($(`.${enlace}`)).prop("checked",false);}else{$('#lista-guias li').not($(`.${enlace}`)).fadeIn()
$('.filtro__item').not($(`.${enlace}`)).prop("checked",false);}})})