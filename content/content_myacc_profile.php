



<?php

// $_POST == secure ( $_POST ); // sécurisation contre injections SQL?
$form_values_valid = false;
$login=$_SESSION['login'];

if (isset($_POST ["email"]))
    $email = $_POST ["email"];
else
    $email = "";
if (isset($_POST ["equipe"]))
    $equipe = $_POST ["equipe"];
else
    $equipe = "";
if (isset($_POST ["categorie"]))
    $categorie = $_POST ["categorie"];
else
    $categorie = "";
if (isset($_POST ["sport"]))
    $sport = $_POST ["sport"];
else
    $sport = "";



if (isset($_POST ["login"]) && $_POST ["login"] != "") {

    $test = Utilisateur::getUtilisateur($dbh, $login);
    if (!is_null($test)) {
        if ($test->testerMdp($_POST ['omdp'])) {
            if($_POST ["email"] == "" || $_POST ["equipe"] == ""){

                $sth = $dbh->prepare("UPDATE `utilisateurs` SET `sport`=? `categorie`=? WHERE `login`=? ");
                $sth->execute(array(
                    $login,
                    $_POST ['sport'],
                    $_POST ['categorie'],$_SESSION ['login'] 
                  
                ));
               
            
        }
       
           if($_POST ["email"] != ""){

                $sth = $dbh->prepare("UPDATE `utilisateurs` SET `email`=? WHERE `login`=? ");
                $sth->execute(array(
                    $_POST ['email'],$_SESSION ['login'] 
                ));
              
                $form_values_valid =TRUE;
        } 
        if($_POST ["equipe"] != ""){

                $sth = $dbh->prepare("UPDATE `utilisateurs` SET `equipe`=? WHERE `login`=? ");
                $sth->execute(array(
                    $_POST ['equipe'],$_SESSION ['login'] 
                ));
               
            $form_values_valid =TRUE;
        } 
 
        
        
        
        
        
        }
    }
}


    echo '<div class="container">
	
      <form action="?page=myacc " method="post" class="form-signin" 
      		oninput="mdp2.setCustomValidity(mdp2.value != mdp1.value ?' . ' Les mots de passe diffèrent.' . ' : \'
	\')"> ';

    echo '<h2 class="form-signin-heading">Mon compte</h2>
                <div class="col-md-2 personal-info">
                </div>
		<div class="col-md-3 personal-info">	
		<input type="text" id="login" name="login" value="' . $login . '" class="form-control" placeholder="Login" required>
		<label for="oldpass">Ancien mot de passe:</label>
                <input type="password" id="oldpassword" name="omdp" class="form-control" required>	
                
                <input type="email" id="inputEmail" name="email" value="' . $email . '" class="form-control" placeholder="Adresse email" required autofocus>
                <select id="sport" name="sport" value="' . $sport . '" class="form-control" placeholder="Sport" required>
					<option value="basketball">Basketball</option>
					<option value="volleyball">Volleyball</option>
					<option value="football">Football</option>	
						
				</select>
                <select id="categorie" name="categorie" value="' . $categorie . '" class="form-control" placeholder="Catégorie" required>
					<option value="coach">Coach</option>
					<option value="joueur">Joueur</option>
					<option value="supporter">Supporter</option>	
						
		</select>
		
		<input type="text" id="equipe" name="equipe" value="' . $equipe . '" class="form-control" placeholder="Equipe" required>
			

                <button class="btn btn-lg btn-primary btn-block" type="submit">Modifier</button>
      
 </div>
</form>
	  
    </div> <!-- /container -->';
    if($form_values_valid ==TRUE){
        echo 'Modifications effectuées';
    }
  
?>

