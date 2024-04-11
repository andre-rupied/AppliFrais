<?php
$action = lireDonneeUrl('action', 'demandeConnexion');

switch($action){
	case 'demandeConnexion':{
		include("vues/v_debutContenu.php");
		include("vues/v_connexion.php");
		break;
	}
	case 'valideConnexion':{
		$login = lireDonneePost('login');
		$mdp = lireDonneePost('mdp');
		$check = $pdo->getInfosVisiteurHash($login);
		if(!password_verify($mdp,$check['mdp'])){
		$hash_mdp = password_hash($mdp,  PASSWORD_ARGON2I);
		$visiteur = $pdo->getInfosVisiteur($login,$hash_mdp);
		if(!password_verify($mdp ,$hash_mdp)){
			ajouterErreur("Login ou mot de passe incorrect", $tabErreurs);
		  include("vues/v_debutContenu.php");
			include("vues/v_erreurs.php");
			include("vues/v_connexion.php");
		}
		else{
			$visiteur = $pdo->getInfosVisiteur($login,$check['mdp']);
			$id = $visiteur['id'];
			$nom =  $visiteur['nom'];
			$prenom = $visiteur['prenom'];
			connecter($id,$nom,$prenom);
            include("vues/v_sommaire.php");
		    include("vues/v_debutContenu.php");
			include("vues/v_accueil.php");
		}
		break;
	}
	default :{
        deconnecter();
		include("vues/v_debutContenu.php");
		include("vues/v_connexion.php");
		break;
	}
}
?>