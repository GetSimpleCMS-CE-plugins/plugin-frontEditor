<?php

# get correct id for plugin
$thisfile = basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile, //Plugin id
	'frontEditor', 	//Plugin name
	'1.0', 		//Plugin version
	'Multicolor',  //Plugin author
	'http://www.ko-fi.com/multicolorplugins', //author website
	'Frontend Editor based on tinymce and bootstrap', //Plugin description
	'plugins', //page type - on which admin tab to display
	''  //main function (administration)
);

# activate filter 
add_action('index-pretemplate', 'frontEdit');

# functions
function frontEdit(){
	global $SITEURL;
	global $content;
	if (isset($_COOKIE['GS_ADMIN_USERNAME'])) {
		$cookie_user_id = _id($_COOKIE['GS_ADMIN_USERNAME']);
		if (file_exists(GSUSERSPATH . $cookie_user_id . '.xml')) {
			$content = '<script src="' . $SITEURL . 'plugins/frontEditor/tinymce/tinymce.min.js"></script>

			<div contenteditable="true" id="editor" style="border:dashed 1px #ddd">' . $content . '</div>
			
			<form method="post" id="contentSaver">
				<textarea class="content-input" name="content" style="display:none"></textarea>
			</form>
		<style>{background-color:#339900;}</style>
			<script>
			document.addEventListener("DOMContentLoaded", () => {
				tinymce.init({
					selector: "#editor",
					inline: true,
					menubar: false,
					autosave_ask_before_unload: false,
					plugins: [
						"advlist", "anchor", "autolink", "autoresize", "autosave", "charmap", "code", 
						"codesample", "directionality", "emoticons", "fullscreen", "help", "image", 
						"importcss", "insertdatetime", "link", "lists", "media", "nonbreaking", 
						"pagebreak",   "quickbars", "searchreplace", "table", 
						"template", "visualblocks", "visualchars", "wordcount","textcolor","visualblocks","grid","save"
					],
					toolbar: " blocks visualblocks | undo redo  bold italic underline link | bullist numlist | image media  table  charmap emoticons code preview  searchreplace  insertdatetime | pagebreak | forecolor backcolor grid_insert save",
					grid_preset: "Bootstrap5",

					save_onsavecallback: function () {
						// Znajdź formularz, w którym znajduje się TinyMCE
						var form = document.getElementById("contentSaver");
						// Wywołaj submit formularza
						form.submit();
					},
					
					content_style: `
						button[data-mce-name="save"] {
							background-color: #4CAF50 !important;
							color: white !important;
						}
						button[data-mce-name="save"] .tox-icon svg path {
							fill: white !important;
						}
					`,

					valid_elements: "*[*]",
					extended_valid_elements: "*[*]",
						paste_as_text: true,
						file_picker_types: "image",
						forced_root_block: false,
						language: "en",
						// Optional: Additional settings for specific plugins
						autoresize_bottom_margin: 20, // Padding for autoresize
						image_uploadtab:false, 
						templates: [
							{ title: "Basic Template", description: "Simple template", content: "<p>Sample content</p>" }
						],
		 
						file_picker_callback: function(callback, value, meta) {
							if (meta.filetype === "image") {
								// Otwórz własny popup z listą zdjęć (np. modale z miniaturkami)
								const win = window.open("' . $SITEURL . 'plugins/frontEditor/filebrowser/imagebrowser.php", "File Picker", "width=800,height=600");
								
								window.addEventListener("message", function receiveFile(event) {
									if (event.origin !== window.location.origin) return;
									callback(event.data.url);  
									window.removeEventListener("message", receiveFile);
									win.close();
								});
							}
						},

					setup: (editor) => {
						editor.on("init", (e) => {
							setInterval(()=>{
							document.querySelector(".content-input").value = editor.getContent();
							},1000);
						});
					},
				});

			});
			</script>';
		}

		} else {
			$content = $content;
		}
?>

<?php
		global $url;
		$xmlFile = GSDATAPAGESPATH . $url . '.xml';

		// Sprawdź, czy dane zostały przesłane przez POST
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
			// Pobierz dane z textarea i zabezpiecz je
			$newContent = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');

			// Wczytaj plik XML
			if (!file_exists($xmlFile)) {
				die('Plik XML nie istnieje.');
			}

			$xml = simplexml_load_file($xmlFile);
			if ($xml === false) {
				die('Błąd wczytywania pliku XML.');
			}

			// Zaktualizuj zawartość elementu <content>
			$xml->content = $newContent; // SimpleXMLElement automatycznie koduje dane

			// Zapisz zmiany do pliku XML
			$result = $xml->asXML($xmlFile);
			if ($result === false) {
				die('Błąd zapisu do pliku XML.');
			}

			echo '<script>window.location.href = window.location.href + "?t=" + Date.now();</script>';
		} else {

		}
	}
?>