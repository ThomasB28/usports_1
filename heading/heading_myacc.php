
      
      
      
      <!doctype html>
<html lang="en-US">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Type" content="text/html">
  <title>jQuery Vertical Tabbed Content Sections</title>
  <link rel="stylesheet" type="text/css" media="all" href="css/css_equipe.css">
  <link rel="stylesheet" type="text/css" media="all" href="css/font-awesome.min.css">
  <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
</head>

<script>
$(function(){
  $('#sidemenu a').on('click', function(e){
    e.preventDefault();

    if($(this).hasClass('open')) {
      // do nothing because the link is already open
    } else {
      var oldcontent = $('#sidemenu a.open').attr('href');
      var newcontent = $(this).attr('href');
      
      $(oldcontent).fadeOut('fast', function(){
        $(newcontent).fadeIn().removeClass('hidden');
        $(oldcontent).addClass('hidden');
      });
      
     
      $('#sidemenu a').removeClass('open');
      $(this).addClass('open');
    }
  });
});
</script>