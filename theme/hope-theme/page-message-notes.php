<?php
/*
Template Name: Message Notes for app page
*/
?>



<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Message Notes</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
        <link rel="stylesheet" href="<?php echo(get_stylesheet_directory_uri() . '/css/app/page-appfeed-messagenotes.css'); ?>"/>
        <script type="text/javascript">
        /* <![CDATA[ */
        var controllerUrl = '<?php echo(get_stylesheet_directory_uri() . "/app/message-notes/controller.php"); ?>';
        /* ]]> */
        </script>  
    </head>
    <body>
           <div class="instructions">
              <p>Send these notes to yourself before Thursday, or they will be lost.</p>
            </div>  

        <div class="hero-unit">
        <div id="appNotes-success" data-alert class="alert-box success radius">
          Your notes were sent!
          <a href="#" class="close">&times;</a>
        </div>
        <div id="appNotes-error" data-alert class="alert-box warning radius">
          There was an error, please try again.
          <a href="#" class="close">&times;</a>
        </div>

          <h1><?php echo(the_title()); ?></h1>
          
          <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?> 

            <div id="appNotes-staticNotes">

              
                  <?php echo(the_content()); ?>
              
            </div>

            <form id="notesForm">
              <a href="#" id="appNotes-sendbtn" class="button">Send these notes to me</a>            
              <div id="appNotes-email">              
                <p>Please enter your email address and we will send your notes to you.</p>
                <p><input type="text" id="email" name="email" /></p>
                <input id="appNotes-notesSubmit" type="submit" value="Email" class="button" />
                <input type="hidden" name="messageDate" value="<?php the_modified_date('Y/m/d'); ?>" />
                <!-- <a href="#" id="appNotes-emailbtn" class="button">Email</a> -->                
              </div>
              <div id="appNotes-bground">
                  <textarea name="notes" id="notes"></textarea>
              </div>
            </form>

          </div>

        <?php endwhile; endif; ?>

        <script src="<?php echo(get_stylesheet_directory_uri() . '/js/app/app-message-notes.js'); ?>"></script>

</body>
</html>
