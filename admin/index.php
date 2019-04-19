<?php


	if(!defined('ABSPATH')) exit('ACCES INTERDIT.');



	class Abyxo_Security_admin_template extends Abyxo_Security
	{
		function __construct( $args = array() )
		{
			echo '<h1>Contacter l\'équipe de Abyxo</h1>';
			echo "<p>Un nouveau projet web ou graphique ? Un besoin d'assistance ?</p>";
			echo "<p>N'hésitez pas, contactez notre équipe via ce formulaire ou par email à l'adresse <a href='mailto:contact@abyxo.agency'>contact@abyxo.agency</a> ou par téléphone au <a href='tel:+33987767778'>09 87 76 77 78</a></p><br><br><br>";


			$this->afficher_cadre_formulaire_contact().
			$this->afficher_cadre_recapitulatif_services_client();
			
		}


		function afficher_cadre_formulaire_contact() {
			echo "<div id='abxsecurity-cadre-formulaire-contact'>";
				echo "<h2>Formulaire de contact</h2>";
				$this->traiter_formulaire_contact();
				echo '<form method="post" enctype="multipart/form-data">';
					echo '<input type="hidden" name="abxsecurity_form_contact" value="true" />';
					echo '<input type="text" name="nom" placeholder="Votre nom" style="text-transform:uppercase" required />';
					echo '<input type="email" name="email" placeholder="Votre adresse email" required />';
					echo '<select name="importance">';
						echo '<option value="Pas de date déterminée">Pas de date déterminée</option>';
						echo '<option value="Dans le mois qui vient">Dans le mois qui vient</option>';
						echo '<option value="Dans les jours qui viennent">Dans les jours qui viennent</option>';
						echo '<option value="Urgent">Urgent</option>';
					echo '</select>';

					echo '<input type="file" name="fichier_joint_1" />';
					echo '<input type="file" name="fichier_joint_2" />';

					echo '<textarea name="description" placeholder="Expliquez nous votre besoin en quelques lignes" required></textarea>';

					echo '<input type="submit" value="Envoyer" />';
				echo '</form>';
			echo "</div>";
		}


		function traiter_formulaire_contact() {
			if(@$_POST['abxsecurity_form_contact'] != true) return;


			//UPLOAD DES FICHIERS JOINTS
			$file1 = $this->form_traiter_input_type_file("fichier_joint_1", plugin_dir_path(__FILE__).'../assets/uploads/');
			$file2 = $this->form_traiter_input_type_file("fichier_joint_2", plugin_dir_path(__FILE__).'../assets/uploads/');

			$attachments = array();
			if($file1 != false) {	//fichier1 déposé sur le serv
				$attachments[] = $file1;
			}
			if($file2 != false) {	//fichier2 déposé sur le serv
				$attachments[] = $file2;
			}

			var_dump($attachments);

			//ENVOIE EMAIL
			$email_object = "Assistance {http://{$_SERVER['HTTP_HOST']} - {$_POST['nom']} - [{$_POST['importance']}]";
			$email_body = "
								Votre Nom: {$_POST['nom']}\n\r
								Votre email: {$_POST['email']}\n\r
								Nom de domaine: http://{$_SERVER['HTTP_HOST']}\n\r
								Degré d'importance: {$_POST['importance']}\n\r
								Expliquez nous votre besoin: {$_POST['description']}\n\r
							";


			
		   	$headers = 'From: '.$_POST['nom'].' <'.$_POST['email'].'>' . "\r\n";
		   	$email_sent = wp_mail("contact@abyxo.agency", $email_object, $email_body, $headers, $attachments);

		   	if($email_sent)
		   		echo "<center><b>Votre message a bien été envoyé à notre équipe, nous
reviendrons vers vous dans les meilleurs délais</b></center>";
		   	else
		   		echo "<center><b>Une erreur est survenue lors de l'envoie de ce message, merci de nous contacter directement par email à cette adresse : contact@abyxo.agency</b></center>";
		}


		function form_traiter_input_type_file($input_name, $target_dir = "", $max_file_size = 5000000) {
			$inputfile_name = $input_name;
			if($target_dir == "")
				$target_dir = plugin_dir_path(__FILE__);

			if(isset($_FILES[$inputfile_name])) {
				$target_file = $target_dir  . uniqid() .  '__' . basename($_FILES[$inputfile_name]["name"]) ;

				// Check taille fichier
				if ($_FILES[$inputfile_name]["size"] > $max_file_size ) {	//5Mb max
				    return false;
				}
				//	Upload
				if (move_uploaded_file($_FILES[$inputfile_name]["tmp_name"], $target_file)) {
			        return $target_file;
			    } else {
			        return false;
			    }
			}

			return false;
		}


		function afficher_cadre_recapitulatif_services_client() {
			$data = $this->getInfosClient();	//requete API google via notre api
			


			echo '<div id="abxsecurity-cadre-recap-services">';
				echo '<h2><center>Vos services chez Abyxo</center></h2>';
				if(!$data) {
					echo '<center>Ce module n’est pas encore activé, vous pouvez demander à l’équipe d\'Abyxo de le mettre en marche.</center></div>';
					return;
				}

				
				echo '<p class="abx-points-gestion">Vous disposez de <b>'.$data->points.' points de gestion</b></p>';
				echo '<ul>Vos services...';
					for($i=1;$i<=30;$i++) {
						if(isset($data->{'service_'.$i}))
							echo '<li>→ '.$data->{'service_'.$i}.'</li>';
					}

					if( get_option( 'abxsecurity_afficherbandeau') == 'true' )
						echo '<li><br><center><b>Bandeau d\'affiliation actif.</b></center></li>';
					else
						echo '<li><br><center><b>Bandeau d\'affiliation désactivé.</b></center></li>';
				echo '</ul>';
			echo '</div>';
		}


		//	Retourne un objet contenant les services et crédits du client via son nom de domaine
		function getInfosClient() {
			$api_endpoint = "http://abyxo-admin.fr/api_abyxosecurity/?domaine=".$_SERVER['HTTP_HOST'];
			$data = file_get_contents($api_endpoint);
			$data = json_decode($data);
			if($data->success == true) {
				//on met a jour le bandeau
				$afficher_bandeau = $data->result->bandeau;
				$str_db = "false";
				if($afficher_bandeau==1||$afficher_bandeau==true||$afficher_bandeau=='1')
					$str_db = "true";
				update_option( 'abxsecurity_afficherbandeau', $str_db );


				//on retourne les resultats
				return $data->result;
			}
			else
			{
				//$raison_erreur = $data->result;	//conteint un message d'explication.
				return false;
			}
		}
		
		
	}