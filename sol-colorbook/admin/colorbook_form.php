<?php
defined( 'ABSPATH' ) or die( 'No!' );
?>
<div style="float:left; width:65%">
    <h2>SOL Coloring Book</h2>
    <?php
    global $wpdb;

    $showform=true;
    $do_more = $message = "";

    //active is an enum (y,n), colorpageurl(url location) and colorpagename(the label) are varchar
    $arr_c = array('id', 'colorpagename', 'colorpageurl', 'active');
    foreach ($arr_c as $val) {//clear the vars
        ${$val}='';
    }

    $meth="Add";
    $data=array();
    if (!empty($_POST)) {
        extract($_POST);
        foreach ($arr_c as $val) {
            if ($val!='id') {//dont need to double dip (set data as) the primary id
                $data[$val]=${$val};
                //echo 'saving '.$val.' = '.${$val}."<br />";//*****
            }
        }

        if($_POST['meth'] == 'Add' || $_POST['meth'] == "Edit") {  //add
            if ( ! function_exists( 'wp_handle_upload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }

            function allow_svg_upload_mimes( $mimes ) {//needed to allow svg's to upload
                $mimes['svg'] = 'image/svg+xml';
                return $mimes;
            }
            add_filter( 'upload_mimes', 'allow_svg_upload_mimes' );

            $uploadedfile = $_FILES['colorpageurl'];
            $upload_overrides = array( 'test_form' => false );
            $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

            if ( $movefile && !isset( $movefile['error'] ) ) {
                echo "SVG was successfully uploaded.\n";
                //echo '<pre>'.print_r( $movefile,true).'</pre>';//******

                $showform=false;
                $data['colorpagefile']=$movefile['file'];
                $data['colorpageurl']=$movefile['url'];
                if ($_POST['meth'] == 'Edit') {  //edit
                    $showform=true;
                    //echo '<pre>'.print_r( $data,true).'</pre>';//******
                    $wpdb->update('sol_colorpages',$data,array('id'=>$_GET['id']));
                } else {
                    $wpdb->insert('sol_colorpages',$data);
                }
                echo "<br />Data saved.<br />";
            } else {
                echo $movefile['error'];//*****     // @see _wp_handle_upload() in wp-admin/includes/file.php
            }
            echo '<br><br><a href="'.admin_url().'admin.php?page=colorbook_slug&meth=Add">Add a New Coloring Page</a><br>';
        }
        $message.="<br />Data updated.";
    }

    if (!empty($_GET['meth'])) {
        if($_GET['meth'] == 'Edit') {  //editing -- loads the data into the form by retrieving the row by the id of the "Edit" they clicked on
            $arr_data = $wpdb->get_results("SELECT * FROM sol_colorpages WHERE id=".$_GET['id']);
            if (!empty($arr_data)) {
                foreach ($arr_data as $d) {
                    foreach ($d as $key => $value) {
                        ${$key}=$value;
                    }
                }
                $meth = "Edit";//needed for the form below to submit it as an edit instead of an add - don't want it to add a duplicate
            }
        }
    }
    if(!empty($_GET["del"])){   
        //delete the file
        if ($arr_del = $wpdb->get_row("SELECT colorpagefile as deletefile FROM sol_colorpages WHERE id = '".$_GET["del"]."' LIMIT 1",ARRAY_A)) {
            extract($arr_del);//produces var deleteurl
            //echo "delfile =  ".$deletefile."<br>";//*****         http://www.mysummersol.com/wp-content/uploads/2015/04/family2.svg
            //$deletefile = str_replace(get_bloginfo('url'), '/', $deletefile);//or is it 'wpurl' in bloginfo
            //echo "stripped =  ".$deletefile."<br>";//*****
            unlink($deletefile);
        }
        //delete from db
        if ($resultD = $wpdb->query("DELETE FROM sol_colorpages WHERE id = '".$_GET["del"]."' LIMIT 1")) {
            $showform=false;
            echo "<br />Data deleted.<br />";
        } else {
            echo "<br />Data deletion failed" . mysql_error();
        }
        echo '<br><br><a href="'.admin_url().'admin.php?page=colorbook_slug&meth=Add">Add a New Coloring Page</a><br>';
    }
    if(!empty($_GET["deact"])){   //deactivate the svg
        $showform=false;
        $data = array('active'=>0);
        if ($arr_do = $wpdb->update('sol_colorpages',$data,array('id'=>$_GET['deact']))) {
            echo "<br />Deactivated the page";
        } else {
            echo "<br />Unable to deactivate";
        }
        echo '<br><br><a href="'.admin_url().'admin.php?page=colorbook_slug&meth=Add">Add a New Coloring Page</a><br>';
    }
    if(!empty($_GET["act"])){   //activate the svg
        $showform=false;
        $data = array('active'=>1);
        if ($arr_do = $wpdb->update('sol_colorpages',$data,array('id'=>$_GET['act']))) {
            echo "<br />Activated the page";
        } else {
            echo "<br />Unable to activate";
        }
        echo '<br><br><a href="'.admin_url().'admin.php?page=colorbook_slug&meth=Add">Add a New Coloring Page</a><br>';
    }

    if ($showform) {
    ?>
        <div class="wrap">
            <hr>
            <form id="colorbook_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>"  enctype="multipart/form-data">
                <input type="hidden" name="meth" value="<?=$meth;?>"/>
                <input type="hidden" name="id" value="<?=$id;?>"/>
                <table>
                    <tr>
                        <td align="right"><b>Label for Coloring Page:</b></td>
                        <td><input type="text" id="colorpagename" name="colorpagename" value="<?=stripslashes($colorpagename);?>" size="30" maxlength="50"></td>
                        <td>This is a short, preferably one word, link to display for the image.</td>
                    </tr>
                    <tr>
                        <td align="right"><b>Upload the SVG:</b></td>
                        <td><input type="file" id="colorpageurl" name="colorpageurl" value="<?=stripslashes($colorpageurl);?>"/></td>
                        <td>SVG size is normally set at 600 x 800 pixels. Other formats may look distorted.</td>
                    </tr>
                </table>
                                <br />
                <p class="submit" style="margin-left:300px;">
                    <input class="button-primary" type="submit" name="Submit" value="Save Page" />
                    <?=$do_more;?>
                </p>
            </form>
        </div>
            <?php
    }
    //end of showform
    //always show the list below

    $arr_editpages = $wpdb->get_results("SELECT id,colorpagename,active FROM sol_colorpages ORDER BY id");
    if ($arr_editpages) {
    ?>
    <hr>
    <h3>Current Coloring Book Pages</h3>
    <table class="wp-list-table widefat">
        <tr>
            <th><b>Coloring Page Label</b></th>
            <th><b>Active</b></th>
            <th></th>
            <th></th>
        </tr>

        <?php
        foreach ($arr_editpages as $pg) {
            $str_active = 'Yes &nbsp; <a href="?page=colorbook_slug&deact='.$pg->id.'">Deactivate</a>';
            if ($pg->active==0) $str_active='No  &nbsp;  <a href="?page=colorbook_slug&act='.$pg->id.'">Activate</a>';
            echo '
                <tr>
                    <td><a href="?page=colorbook_slug&meth=Edit">'.stripslashes($pg->colorpagename).'</a></td>
                    <td>'.$str_active.'</td>
                    <td>
                        <a href="?page=colorbook_slug&meth=Edit&id='.$pg->id.'">Replace SVG</a>
                    </td>
                    <td>';
            if (($pg->id) > 3) {//can not delete the defaults
                echo '
                        <a href="?page=colorbook_slug&del='.$pg->id.'">Delete</a>';
            }
            echo '
                    </td>
                </tr>'."\n";
        }
        ?>
    </table>
    <?php
    }
    ?>
</div>
<div class="colorbook-sidebar">
    <h3>Instructions:</h3>
    1. Use the form to upload the SVG and give it a label. You will notice that three default images have been provided and will work out of the box. <br />
    2. Then on the page where you want the coloring book to appear add the following shortcode:  <br>
    [colorbook]
    <br />
    
    <hr>
    <?php
    include plugin_dir_path(__FILE__).'../includes/credits.php';
    ?>
</div>