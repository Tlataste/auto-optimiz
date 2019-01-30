<?php
/*
Plugin Name: Auto-Optimiz
Description: SEO Optimizator 8000
Version: 0.1
Author: Lataste Théo
License: GPL2
*/


/* Add Metabox */

// wordToReplace : textField (texte à remplacer)
// newWord : textField (texte remplacant)


add_action('admin_enqueue_scripts', 'cmb_add_admin_styles' );
add_action('add_meta_boxes','initialisation_metaboxes');



function cmb_add_admin_styles() {
    wp_enqueue_style('cmb-admin', plugins_url( 'Auto-Optimiz/css/admin.css' ) );
}




function initialisation_metaboxes(){
    //on utilise la fonction add_metabox() pour initialiser une metabox
    add_meta_box('id_ma_meta', 'Auto Keyword v1.0', 'ma_meta_function', 'post', 'side', 'high');
}

function ma_meta_function(){
?>

    <div class="auto-keyword">
        <div class="tl-row">
            <label for="to-insert">Mot clé : </label>
            <input id="to-insert" type="text" name="to-insert" value="J'insère une phrase."/>
        </div>

        <select id="type">
            <option value="occurences">Occurences</option>
            <option value="densite">Densité %</option>
        </select>

        <div class="tl-row occurence-number">
            <label for="occurence">Occurrences : </label>
            <input id="occurence" type="text" name="occurence" value=""/>
        </div>

        <div class="tl-row densite-number" style="display:none">
            <label for="densite">Densité % : </label>
            <input id="densite" type="number" name="densite" value="10"/>
        </div>

        <div>
            <input type="checkbox" for="phraseOnly" id="phraseOnly">
            <label for="phraseOnly">Insertion de phrase</label>
        </div>

        <div class="text-length">
            L'article fait <span><?php echo word_count(); ?></span> mots au total.
        </div>

        <div class="selected-length" style="margin-bottom:15px;">

        </div>

        <button class="submit button button-primary button-large">Mettre à jour</button>
    </div>


    <script>
        $(document).ready(function() {
            $('#type').on('change', function (e) {
                var optionSelected = $("#type option:selected", this);
                var valueSelected = this.value;
                if(valueSelected == "densite") {
                    $('.occurence-number').hide();
                    $('.densite-number').show();
                }
                else if (valueSelected == "occurences") {
                    $('.occurence-number').show();
                    $('.densite-number').hide();
                }
            });

            $('.auto-keyword button.submit').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                var toInsert = $('#to-insert').val();

                if(toInsert != ""){
                    if($('#type option:selected').text() == "Occurences") {
                        insertKeywords($('#occurence').val(), toInsert);
                    }

                    if($('#type option:selected').text() == "Densité %") {
                        var selectedText = tinyMCE.activeEditor.selection.getContent( {format : "html"} );
                        if ( selectedText != "" ){
                            var wordCount = selectedText.split(/\s+/).length;
                            var nbOccurence = wordCount * ($('#densite').val()/100);
                            insertKeywords(Math.ceil(nbOccurence), toInsert);
                        }
                        else {
                            var wordCount = $('.auto-keyword .text-length span').text();
                            var nbOccurence = wordCount * ($('#densite').val()/100);
                            insertKeywords(Math.ceil(nbOccurence), toInsert);
                        }
                    }
                }
            });

            window.setInterval(
                function(){
                    var selectedText = tinyMCE.activeEditor.selection.getContent( {format : "text"} );
                    if ( selectedText != "" ){
                        $('.selected-length').html('Le texte sélectionné fait <span>'+ selectedText.split(" ").length +'</span> mots.');
                    }
                    else {
                        $('.selected-length').html('');
                    }
                },
                1000
            );
        });


        function getRandomInt(max) {
            return Math.floor(Math.random() * Math.floor(max));
        }

        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        function minimizeFirstLetter(string) {
            return string.charAt(0).toLowerCase() + string.slice(1);
        }

        function insertKeywords(nbOccurence, keyword) {
            //console.log("insert Keywords");
            var toInsert = keyword;
            var selectedText = tinyMCE.activeEditor.selection.getContent( {format : "html"} );
            var nbOccurences = nbOccurence;

            // Si du texte a été sélectionné
            if ( selectedText != "" ){

                if($('#phraseOnly').is(':checked')) {
                    //console.log('phrase only');
                    var splittedText = selectedText.split(".");
                    for(i = 0; i < nbOccurences; i++) {
                        var random = getRandomInt(splittedText.length);
                        if(splittedText[random-1] != toInsert) {
                            splittedText[random-1] = splittedText[random-1] + ".";
                        }
                        splittedText.splice(random, 0, toInsert);
                    }
                }
                else {
                    var splittedText = selectedText.split(/\s+/);
                    for(i = 0; i < nbOccurences; i++) {
                        var random = getRandomInt(splittedText.length);
                        if(splittedText[random-1].substr(splittedText[random-1].length - 1) == ".") {
                            splittedText[random] = minimizeFirstLetter(splittedText[random]);
                            splittedText.splice(random, 0, capitalizeFirstLetter(toInsert));
                        }
                        else {
                            splittedText.splice(random, 0, toInsert);
                        }
                    }
                }

                var newText = splittedText.join(" ");
                var mainText = tinyMCE.activeEditor.getContent( {format : "html"} );
                textReplaced = mainText.replace(selectedText, newText);
                tinyMCE.activeEditor.setContent(textReplaced);

            }
            else {

                var text = tinyMCE.activeEditor.getContent( {format : "html"} );

                if($('#phraseOnly').is(':checked')) {
                    //console.log('phrase only');
                    var splittedText = text.split(".");
                    for(i = 0; i < nbOccurences; i++) {
                        var random = getRandomInt(splittedText.length);
                        if(splittedText[random-1] != toInsert) {
                            splittedText[random-1] = splittedText[random-1] + ".";
                        }
                        splittedText.splice(random, 0, toInsert);
                    }
                }
                else {
                    var splittedText = text.split(/\s+/);
                    for(i = 0; i < nbOccurences; i++) {
                        var random = getRandomInt(splittedText.length);
                        if(splittedText[random-1].substr(splittedText[random-1].length - 1) == ".") {
                            splittedText[random] = minimizeFirstLetter(splittedText[random]);
                            splittedText.splice(random, 0, capitalizeFirstLetter(toInsert));
                        }
                        else {
                            splittedText.splice(random, 0, toInsert);
                        }
                    }
                }

                //console.log(splittedText);

                var newText = splittedText.join(" ");
                tinyMCE.activeEditor.setContent(newText);
            }
        }




    </script>


    <?php

}

function word_count() {
    $content = get_post_field( 'post_content', $post->ID );
    $word_count = str_word_count( strip_tags( $content ) );
    return $word_count;
}


// Retourne un tableau de position des occurences
function mb_stripos_all($haystack, $needle) {

    $s = 0;
    $i = 0;
    $nbOccurences = 0;

    while(is_integer($i)) {

        $i = mb_stripos($haystack, $needle, $s);

        if(is_integer($i)) {
            $aStrPos[] = $i;
            $s = $i + 1 ;//mb_strlen($needle);
            $nbOccurences = 1;
        }
    }

    if(isset($aStrPos)) {
        return $aStrPos;
    } else {
        return false;
    }
}
