<ul id="sidemenu">
  <li>
      <a href="#home-content" class="open"><strong>Mon compte</strong></a>
  </li>

  <li>
    <a href="#about-content"><strong>Mes statistiques</strong></a>
  </li>
  
  <li>
    <a href="#ideas-content"><strong>Achievements</strong></a>
  </li>
  
  <li>
    <a href="#mdp-content"><strong>Changer de mot de passe</strong></a>
  </li>
  

</ul> 


<div id="content">
    <div id="home-content" class="contentblock">
        <?php require'content_myacc_profile.php' ?>
      
    </div><!-- @end #home-content -->
    
    
    <div id="about-content" class="contentblock hidden">
      <h1>Tes statistiques maggle</h1>
      <?php require 'content_mystats.php'?>
    </div><!-- @end #about-content -->
    
    <div id="mdp-content" class="contentblock hidden">
        
      <?php require 'content/content_changepw.php' ?>
    </div><!-- @end #ideamdps-content -->
    

</div><!-- @end #content -->