<?php
// run as follows: php index.php [langage] [format]
// language: [de, en, es, fr, nl], default: en
// format: [html, twig], default: html

// determine language and make it available variable
$lang = 'en';
if (isset($argv[1])) {
    $lang = $argv[1];
} else if (array_key_exists('lang', $_GET)) {
    $lang = $_GET['lang'];
}

function is_output_format_twig($argv)
{
    return (isset($argv[2]) and 'twig' === $argv[2]);
}

function load_activities_via_ajax($argv)
{
    return (isset($argv[3]) and 'ajax' === $argv[3]);
}

$isEnglish = false;
if ($lang == 'en') {
    $isEnglish = true;
}

require(get_language_file_path($lang));

// PHP FUNCTIONS

function get_language_file_path($lang) {
    $res = 'lang/index_' . $lang . '.php';
    return $res;
}

function print_if_selected($candidate, $chosen) {
    $res = '';
    if ($chosen == $candidate) {
        $res = 'selected';
    }
    return $res;
}

function get_url_to_index() {
    global $lang;

    return '/' . $lang . '/';
}

?>

<!DOCTYPE html>
<html lang="<?php echo $lang ?>">
<head>
<?php if (is_output_format_twig($argv)) { ?>
    {% if title is not empty %}
        <title>{{ title|raw }}</title>
    {% else %}
        <title>Retromat - <?php echo($_lang['HTML_TITLE']); ?></title>
    {% endif %}
    {% if description is not empty %}
        <meta name="description" content="{{ description|raw }}">
    {% endif %}
<?php } else { ?>
    <title>Retromat - <?php echo($_lang['HTML_TITLE']); ?></title>
<?php } ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1"> 

    <link rel="stylesheet" type="text/css" href="/static/retromat.css" />

<link rel="shortcut icon" href="/static/images/favicon.ico" />
<link rel="apple-touch-icon-precomposed" href="/static/images/apple-touch-icon.png" />

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="/static/jquery.min.js"><\/script>')</script>

<script src="/static/lightbox/lightbox.js"></script>
<link href="/static/lightbox/lightbox.css" rel="stylesheet" />

<!-- Detect IE9 - It has problems, probably because of history.pushState -->
<script>
    var is_ie = false;
</script>
<!--[if lt IE 10 ]>
<script>
    var is_ie = true;
</script>
<![endif]-->

<script type="text/javascript">
// CONFIG
var NUMBER_OF_REGULAR_PHASES = 5;
var PHASE_SOMETHING_DIFFERENT = 5;
var INVERTED_CHANCE_OF_SOMETHING_DIFFERENT = 25; // Probability to show "different" phase is 1:INVERTED_CHANCE
var PHASE_ID_TAG = 'phase';
</script>

<script src="/static/lang/phase_titles_<?php echo $lang ?>.js"></script>
<?php if (!load_activities_via_ajax($argv)) { ?>
    <script src="/static/sources.js"></script>
    <script src="/static/lang/activities_<?php echo $lang ?>.js"></script>
<?php } ?>
<script src="/static/lang/photos.js"></script>
<script src="/static/functions.js"></script>

    <script type="text/javascript">
        // Functions that need translations from PHP.
        // @todo save bandwidth by moving these functions to functions.js,
        // which is cached by browsers and crawlers.

        //Input: String
        function publish_plan(plan_id, phase) {
            var plan_id = sanitize_plan_id(plan_id);
            if (plan_id) {
                empty_plan();
                publish_activity_blocks(plan_id);
                enable_phase_browsing();

                if (phase != undefined) {
                    publish_plan_title("<?php echo($_lang['INDEX_ALL_ACTIVITIES']); ?> " + phase_titles[phase].toUpperCase());
                    hide_phase_stepper();
                } else {
                    show_phase_stepper();
                    hide_plan_title();
                }
                publish_plan_id(plan_id);
            }
        }

        function get_activity_array(index) {
            var activity_array = all_activities[index];
            if (activity_array == null) {
                alert("<?php echo($_lang['ERROR_MISSING_ACTIVITY']); ?> " + convert_index_to_id(index));
            }
            return activity_array;
        }

        /* Param: activity index
         * Returns: String (empty or link to photo(s))
         */
        function get_photo_string(index) {
            res = "";
            if (all_photos[index] != null) {
                for (var i = 0; i < all_photos[index].length; i++) {
                    res += "<a href='";
                    res += all_photos[index][i]['filename'];
                    res += "' rel='lightbox[activity" + index + "]' ";
                    res += "title='<?php echo($_lang['ACTIVITY_PHOTO_BY']); ?>";
                    res += all_photos[index][i]['contributor'];
                    res += "'>";
                    if (i == 0) {
                        if (all_photos[index].length < 2) {
                            res += "<?php echo($_lang['ACTIVITY_PHOTO_VIEW_PHOTO']); ?>";
                        } else {
                            res += "<?php echo($_lang['ACTIVITY_PHOTO_VIEW_PHOTOS']); ?>";
                        }
                    }
                    res += "</a>";
                }
                //        res += " | "; PAUSED Until I've got more time
            }
            return res;
        }

        /************ BEGIN Footer Functions ************/
        function create_link_to_all_activities(number_of_activities) {
            var link_string = "<a href='?id=1";
            for (i = 2; i <= number_of_activities; i++) {
                link_string += "-" + i;
            }
            return link_string + "&all=yes'>" + number_of_activities + "</a>";
        }
        /************ END Footer Functions ************/

        /************ BEGIN PopUps Plan Navigation (Search) ************/
        function publish_activities_for_keywords(keywords) {
            var keywords_array = keywords.split(' ');
            var plan_id = '';
            for (var i = 0; i < keywords_array.length; i++) {
                var sub_ids = search_activities_for_keyword(keywords_array[i]);
                if (sub_ids.length > 0) {
                    plan_id += sub_ids + '-';
                }
            }
            plan_id = plan_id.substr(0, plan_id.length - 1); // Remove trailing '-'

            plan_id += find_ids_in_keyword(keywords);

            var text = '<?php echo($_lang["INDEX_ALL_ACTIVITIES"]) ?>';
            if (plan_id != '') {
                publish_plan(plan_id);
                hide_phase_stepper();
                hide_popup('search');
            } else {
                publish_plan_id(plan_id);
                empty_plan();
                text = '<?php echo($_lang["POPUP_SEARCH_NO_RESULTS"]) ?>';
            }
            publish_plan_title(text + " '" + keywords + "'"); // Call must be after "publish_plan()" or plan_title_container won't be displayed
        }
        /************ END PopUps Plan Navigation ************/

        /************ BEGIN Newsletter Subscribe ************/
    
        var default_value = 'Your email address';

        function removeDefaultText(){
            var email_field = document.getElementById("email_address");
            if(email_field.value == default_value){
                email_field.value = "";
            }
        }

        function setDefaultText(){
            var email_field = document.getElementById("email_address");
            if(email_field.value == ''){
                email_field.value = default_value;
            }
        }

        /************ END Newsletter Subscribe ************/

    </script>

<link rel="alternate" hreflang="en" href="/en/" />
<link rel="alternate" hreflang="es" href="/es/" />
<link rel="alternate" hreflang="fr" href="/fr/" />
<link rel="alternate" hreflang="de" href="/de/" />
<link rel="alternate" hreflang="nl" href="/nl/" />
<link rel="alternate" hreflang="ru" href="/ru/" />
<link rel="alternate" hreflang="zh" href="/zh/" />

<!-- Matomo -->
<script type="text/javascript">
    var _paq = _paq || [];
    /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    (function() {
        var u="//retromat.org/piwik/";
        _paq.push(['setTrackerUrl', u+'piwik.php']);
        _paq.push(['setSiteId', '3']);
        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
        g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
    })();
</script>
<!-- End Matomo Code -->
</head>

<body onload="JavaScript:init()">

    <div class="header">
        <div class="header__leftblock">
            <div class="header__logo">
                <a href="<?php echo(get_url_to_index()) ?>" class="header__logo">
                    <img src="/static/images/retromat-logo.svg"
                     alt="Retromat"
                     title="Retromat">
                </a>
            </div>

            <div class="header__navi">
                <ul>
                  <li><a href="/blog/">Blog</a></li>
                  <li><a href="/en/books">Books</a></li>
                  <li><a href="/en/membership">Membership</a></li>
                  <li><a href="/en/about">About</a></li>
                </ul>
            </div>
        </div>

        <div class="header__languageswitcher"> 
            <select onchange="switchLanguage(this.value)">
                <option value="de" <?php echo(print_if_selected("de", $lang)); ?> >Deutsch (140 Aktivit&auml;ten)</option>
                <option value="en" <?php echo(print_if_selected("en", $lang)); ?> >English (140 activities)</option>
                <option value="es" <?php echo(print_if_selected("es", $lang)); ?> >Espa&ntilde;ol (140 actividades)</option>
                <option value="fr" <?php echo(print_if_selected("fr", $lang)); ?> >Fran&ccedil;ais (64 activit&eacute;s)</option>
                <option value="nl" <?php echo(print_if_selected("nl", $lang)); ?> >Nederlands (101 activiteiten)</option>
                <option value="ru" <?php echo(print_if_selected("ru", $lang)); ?> >Русский (133 упражнений)</option>
                <option value="zh" <?php echo(print_if_selected("zh", $lang)); ?> >中文 (129 活动)</option>
            </select>
        </div>
    </div>

    <div class="pitch">
        <div class="content">
            <div class="inner font-serif">
                <?php echo($_lang['INDEX_PITCH']); ?>  
            </div>
        </div>
    </div>


    <div class="promo">
        <div class="content">
            <div class="inner">
                There's a new Retromat book geared towards beginners!
                <br><br>
                <a href="/blog/new-book-15-plans-for-retrospectives/"
                   class="button-medium" style="color: white">
                   Check out "Plans for Retrospectives"
                </a>
                <br><br>
                (<a href="{{ path('print') }}" class="small-font">Looking for the Print Retromat?</a>)
            </div>
        </div>
    </div>

    <div class="plan-header">
        <div class="content">
            <div class="inner">
                <div class="ids-display">
                    <div class="print-header font-serif">
                        Retromat.org – by Corinna Baldauf
                    </div>

                    <?php if (is_output_format_twig($argv)) { ?>
                        {% include 'home/header/idDisplay.html.twig' %}
                    <?php } else { ?>
                        <?php echo($_lang['INDEX_PLAN_ID']); ?>
                        <form name="js_ids-display__form" class="ids-display__form" action="JavaScript:publish_plan($('.ids-display__input').val());">
                            <input type="text" size="18" name="js_display" class="ids-display__input" value="">
                        </form>
                    <?php } ?>
                </div>
                
                <div class="plan-header-inner-right">
                    <div class="plan-navi" style="overflow: visible;">
                       <ul>
                            <li>
                                <a class="plan-navi__random" title="<?php echo($_lang['INDEX_RANDOM_RETRO']); ?>" href="JavaScript:publish_random_plan()">
                                    <?php echo($_lang['INDEX_RANDOM_RETRO']); ?>         
                                </a>
                            </li>
                            <li>
                                <a class="plan-navi__search" title="<?php echo($_lang['INDEX_SEARCH_KEYWORD']); ?>" href="JavaScript:show_popup('search');">
                                    <?php echo($_lang['INDEX_SEARCH_KEYWORD']); ?>                       
                                </a>
                                <div class="js_popup--search popup--search popup">
                                    <form action="JavaScript:publish_activities_for_keywords($('.js_popup--search__input').val())" name="js_search_form" class="search_form">
                                        <input type="text" size="12" name="js_popup--search__input" class="js_popup--search__input popup__input" value="">
                                        <input type="submit" class="popup__submit" value="<?php echo($_lang['POPUP_SEARCH_BUTTON']); ?>">
                                        <a href="JavaScript:hide_popup('search');" class="popup__close-link"><?php echo($_lang['POPUP_CLOSE']); ?></a>
                                    </form>
                                    <div class="popup__info"><?php echo($_lang['POPUP_SEARCH_INFO']); ?></div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="plan-header-promo">
                        <a href="/en/books" target="_blank">
                            <img src="/static/images/sponsors/ebook-retromat-sponsor.png" width="178" height="95" alt="Did you know there's are Retromat eBooks?">
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php if (is_output_format_twig($argv)) { ?>
        {% include 'home/titles/planTitle.html.twig' %}
    <?php } else { ?>
        <div class="js_plan_title_container plan-title display_none">
            <div class="content">
                <div class="inner">
                   <span class="js_fill_plan_title">Replaced by JS</span>
               </div>
            </div>
        </div>
    <?php } ?>


    <?php if (is_output_format_twig($argv)) { ?>
        {% include 'home/activities/activities.html.twig' %}
    <?php } else { ?>
        <div class="js_plan">
            <div class="bg1">
                <div class="content">
                    <div class="inner">
                        <?php echo($_lang['INDEX_LOADING']); ?>
                        <noscript>
                            <?php echo($_lang['ERROR_NO_SCRIPT']); ?>
                        </noscript>
                    </div>
                </div>
            </div>
        </div><!-- END plan -->
    <?php } ?>

    <div class="js_activity_block_template js_activity_block display_none">
        <div class="content">
            <div class="activity">
                <div class="phase-stepper js_prev_button">
                    <a href="javascript:Previous" class="js_phase-stepper"
                       title="<?php echo($_lang['ACTIVITY_PREV']) ?>">&#9668;
                    </a>
                </div>
                <div class="activity_content">
                    <div class="js_phase_title phase_title">
                        <a href="#" class="js_fill_phase_link">
                            <span class="js_fill_phase_title"></span>
                        </a>
                    </div>

                    <div class="js_item">
                        <h2 class="font-serif">
                            <span class="js_fill_name">
                            </span>
                            <span class="activity_id_wrapper">
                                    (<a class="js_fill_activity_link" href="#">#<span class="js_fill_id"></span></a>)
                            </span>
                        </h2>
                        <div class="summary">
                            <span class="js_fill_summary">
                            </span>
                            <br>
                            <span class="source"><?php echo($_lang['ACTIVITY_SOURCE']) ?>
                                <span class="js_fill_source"></span>
                            </span>
                        </div>
                        <div class="description">
                            <span class="js_fill_description">
                            </span>
                        </div>
                    </div><!-- END js_item -->

                    <div class="js_photo_link photo_link">
                        <span class="js_fill_photo-link"></span>
                    </div><!-- END .js_photo_link -->

                </div>
                <div class="phase-stepper js_next_button">
                    <a href="Javascript:Next" class="js_phase-stepper"
                       title="<?php echo($_lang['ACTIVITY_NEXT']) ?>">&#9658;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="about">
        <div class="content font-serif">
            <div class="inner">
                <?php echo($_lang['INDEX_ABOUT']); ?>
            </div>
        </div>
    </div>

    <div class="team">
        <div class="content">
            <div class="inner">
            <?php if (!$isEnglish) { ?>
                <?php for($i=0; $i < count($_lang['INDEX_TEAM_TRANSLATOR_LINK']); $i++) { ?>
                <div class="team-member">
                    <a href="<?php echo($_lang['INDEX_TEAM_TRANSLATOR_LINK'][$i]); ?>">
                        <img src="<?php echo($_lang['INDEX_TEAM_TRANSLATOR_IMAGE'][$i]); ?>" width="70" height="93" title="<?php echo($_lang['INDEX_TEAM_TRANSLATOR_NAME'][$i]); ?>" class="team-photo">
                    </a>

                    <h3>
                        <?php echo($_lang['INDEX_TEAM_TRANSLATOR_TITLE']); ?>
                        <a href="<?php echo($_lang['INDEX_TEAM_TRANSLATOR_LINK'][$i]); ?>">
                            <?php echo($_lang['INDEX_TEAM_TRANSLATOR_NAME'][$i]); ?>
                        </a>
                    </h3>

                    <div class="team-text">
                        <?php echo($_lang['INDEX_TEAM_TRANSLATOR_TEXT'][$i]); ?>
                    </div>
                </div><!-- .team--translator -->
               <?php } ?>
            <?php } ?>

                <div class="team-member">
                    <a href="https://www.corinnabaldauf.de/">
                       <img src="/static/images/team/corinna_baldauf.jpg" width="70" height="93" title="Corinna Baldauf" class="team-photo">
                    </a>
                    <h3>
                        <?php echo($_lang['INDEX_TEAM_CORINNA_TITLE']); ?> 
                        <a href="https://www.corinnabaldauf.de/">
                           Corinna Baldauf
                       </a>
                    </h3>
                    <div class="team-text">
                        <?php echo($_lang['INDEX_TEAM_CORINNA_TEXT']); ?>    
                    </div>
                </div><!-- .team--corinna -->

                <div class="team-member">
                    <a href="/en/team/timon">
                        <img src="/static/images/team/timon_fiddike.jpg" width="70" height="93" title="Timon Fiddike" class="team-photo">
                    </a>
                    <h3>
                        <?php echo($_lang['INDEX_TEAM_TIMON_TITLE']); ?> 
                        <a href="/en/team/timon">
                           Timon Fiddike
                        </a>
                    </h3>
                    <div class="team-text">
                          <?php echo($_lang['INDEX_TEAM_TIMON_TEXT']); ?>          
                    </div>
                </div><!-- .team--timon-->

            </div><!-- END .inner-->
        </div>
    </div>


    <div class="footer">
        <ul>
            <li><a href="/blog/faq-frequently-asked-questions/">FAQ</a>
            </li>
            <li><a href="/blog/privacy-policy">Imprint &amp; Privacy Policy</a></li>
        </ul>
    </div>

    <!-- Matomo -->
    <noscript><img src="//retromat.org/piwik/piwik.php?idsite=3&amp;rec=1" style="border:0;" alt="" /></noscript>
    <!-- End Matomo Code -->

</body>
</html>
