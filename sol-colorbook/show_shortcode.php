<?php
/*
can be extracted from $arr_data created in Colorbook.php:

    'colorpageurl' => '[svg url]',            //the svg url to display
    'colorpagename' => 'family beach',		//what to label the color page
    'active'=> 'y',	                        //y|n    if the color page is active (can turn on/off this way)
	            
the js is pulled via load_scripts from Colorbook.php
and depends on the following html to be generated for it to work:      
*/
//$arr_pagedata gives only current colorpageurl

$html.='
<div id="coloring_pages">';
//display all the pages by showing their colorpagename
if (!empty($arr_pages)) {
        //var_dump($arr_pages);//****
    $slug = get_permalink();
    $slug = substr($slug,0,-1);//strip trailing slash
    foreach ($arr_pages as $page) {
        if ($page['colorpagename'] == $_REQUEST['colorpage']) {
            $html.='
    <b>'.$page['colorpagename'].'</b> |';
        } else {
            $html.='
    <a href="'.$slug.'?colorpage='.$page['colorpagename'].'">'.$page['colorpagename'].'</a> |';
        }
    }
    $html = substr($html,0,-1);//strip off last pipe from links
}
$sol_width = get_option('sol_width');
$sol_height = get_option('sol_height');
$html.='
</div>
<div id="coloring_book_image" style="width:'.$sol_width.'px; height:'.$sol_height.'px;">
    <svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" version="1.1" style="height: 100%;" x="0px" y="0px" viewBox="0 0 '.$sol_width.' '.$sol_height.'" enable-background="new 0 0 '.$sol_width.' '.$sol_height.'" id="colorbooklayer" xml:space="preserve">
        <defs>';
 $arr_tip = array("crayon","brush","pencil");
 $arr_colors = array('Red','Tomato','OrangeRed','DeepPink','Pink','DarkSalmon','Orange','Gold','Yellow','GreenYellow','OliveDrab','DarkGreen','Cyan','DeepSkyBlue','Blue','Purple','MediumOrchid','Brown','Maroon','SaddleBrown','BurlyWood','Black','Gray','Silver','White');
 foreach ($arr_tip as $tip) {
    foreach ($arr_colors as $color) {
        $str_pattern = '/assets/images/'.$tip.'-'.strtolower($color).'.jpg';
        $html.='
            <pattern id="img'.$tip.'-'.$color.'" patternUnits="userSpaceOnUse" width="300" height="300"><image xlink:href="'.plugins_url($str_pattern, __FILE__).'" x="0" y="0" width="300" height="300" /></pattern>';
    }
}
$html.='
        </defs>
        <g id="g-code-main">';
//now, depending on the page ie from  ?page=[colorpagename]  load the colorpagename in here (just the code between the <g> tags)
//**************************
//colorpageurl comes from the colorbook.php
if (substr($colorpageurl,0,3)=='/wp') $colorpageurl = get_bloginfo('wpurl').$colorpageurl;//for out of the box defaults to work
if ($colorpage_svg = file_get_contents($colorpageurl)) {
    $svg = new SimpleXMLElement($colorpage_svg);
    foreach ($svg as $key=>$value) {
        //echo $key.' ';//****
        if ($key=="switch") {//AI files have this
            foreach ($value as $k=>$v) {
                //echo $k.' ';//****
                if ($k!="foreignObject" && $k=="g") {
                    foreach ($v as $kg=>$g) {
                        //echo $kg.' ';//****
                        $html.=  $g->asXML();//*****
                    }
                }
            }
        } else {
            $html.=  $value->asXML();//*****
        }
    }
}
$html.='       
        </g>';
//place for logo to appear
$html.='
<g id="colorpage_logo_link">
    <text transform="matrix(1 0 0 1 468.1924 790.5059)" font-family="Lobster" font-size="11.0393">www.mySummerSol.com</text>
    <rect x="457" y="696.365" fill="none" width="134.311" height="83.103"/>
    <text transform="matrix(1 0 0 1 457 771.5146)"><tspan x="0" y="0" fill="#1F8CAA" font-family="Lobster" font-size="107.3264">S  </tspan><tspan x="107.692" y="0" fill="#1F8CAA" font-family="Lobster" font-size="79.7282">l</tspan></text>
    <g>
        <text transform="matrix(1 0 0 1 517.8701 719.8242)" fill="#55C7D9" font-family="Lobster" font-size="19.3188">ummer</text>
    </g>
    <path fill="#F47E2C" d="M523.081,719.773c0,0,6.022,9.354,10.35,7.793c4.676-1.687,6.823-11.27,6.823-11.27
        s-0.767,9.047,3.986,11.041c4.753,1.991,12.368-6.032,12.368-6.032s-5.084,7.334-0.945,11.934
        c4.14,4.601,11.983,0.741,11.983,0.741s-7,4.396-5.314,10.988c1.688,6.595,9.097,5.877,9.097,5.877s-9.709,0.562-11.088,6.234
        c-1.381,5.675,7.819,11.245,7.819,11.245s-11.082-5.162-15.795-0.513c-3.935,3.886,2.301,11.245,2.301,11.245
        s-5.673-6.951-11.039-6.338c-5.366,0.614-6.234,9.303-6.234,9.303s-1.278-10.759-5.878-11.91
        c-4.524-1.13-10.272,5.981-10.272,5.981s2.76-7.819-0.613-11.041c-3.374-3.219-12.063-0.306-12.063-0.306s7.922-4.829,7.156-10.502
        c-0.768-5.673-11.243-6.159-11.243-6.159s11.102-2.422,13.006-7.027c1.841-4.446-7.078-9.94-7.078-9.94s9.761,4.037,12.599,0.818
        C526.535,727.929,523.081,719.773,523.081,719.773z"/>
    <circle fill="#FFC810" cx="538.107" cy="749.262" r="19.778"/>
</g>
';

//**************************
$html.='       
    </svg>
</div>
<div id="colors" class="no-print">
    <p style="display:none;">Color selected: <span id="color_chosen">White</span></p>';
    
foreach ($arr_colors as $color) {
    $html.='
    <input type="button" class="color_choice marker" id="'.$color.'" style="background-color: '.strtolower($color).';">';
}
$html.='
</div>
<div id="styletip" class="no-print">
    <img id="marker" src="'.plugins_url('/assets/images/marker.png', __FILE__).'" width="120" height="26" alt="marker" style="background-color: white;"/>';
reset($arr_tip);
foreach ($arr_tip as $tip) {
    $html.='
    <img id="'.$tip.'" src="'.plugins_url("/assets/images/$tip.png", __FILE__).'" width="120" height="26" alt="'.$tip.'" style="background-color: white;"/>';
}
$html.=' 
    <input type="button" id="undo_redo" value="Undo" class="no-print">
    <input type="button" id="reset_image" value="Start Over" class="no-print">
    <a href="'.$colorpageurl.'" target="_blank" class="button no-print" id="print_colorpage">Print</a>
</div>';

?>
