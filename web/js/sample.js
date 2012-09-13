$(function(){

  $('#buttonDelete').click(function(){
    if(confirm('Are you sure you want to delete this record?\nThis action cannot be undone.')){
      window.location.href = this.value;
    }
  });

});