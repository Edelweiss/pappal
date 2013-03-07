$(document).ready(function(){

  $('#reset').click(function(){
    var form = $('#filter');
    form.find("input[name^='form'][type!='hidden']").each(function(index, input){
      $(input).val('');
    });
    form.find("select[name='form[material]']").val('');
    form.find("select[name^='sort']").val('');

    return false;
  });

});