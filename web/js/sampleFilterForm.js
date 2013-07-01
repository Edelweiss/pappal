$(document).ready(function(){

  $('#reset').click(function(){
    var form = $('#search');
    form.find("input[name^='thumbnail'][type!='hidden']").each(function(index, input){
      $(input).val('');
    });
    form.find("#thumbnail_sample_material").val('');
    form.find("#thumbnail_language").val('');
    form.find("select[name^='sort']").val('');

    return false;
  });

});