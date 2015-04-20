<?php
defined( 'ABSPATH' ) or die( 'No!' );
?>
<div style="float:left; width:65%">
    <h2>SOL Coloring Book: Settings</h2>
    <br />
    <?php
    /*
     * check and see if there are any settings saved in options, if not, set default ones
     */
    $sol_width = get_option('sol_width');
    $sol_height = get_option('sol_height');
    if (empty($sol_width)) $sol_width = 600;
    if (empty($sol_height)) $sol_height = 800;
    
    if (!empty($_POST)) {
        extract($_POST);                                                        //sol_width and sol_height over-write
        if ($_POST['meth'] == 'save') {
            update_option('sol_width', $sol_width);
            update_option('sol_height', $sol_height);
            echo 'Settings saved.<br>';
        }
    }
    ?>
    <form id="colorbook_settings_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>"  enctype="multipart/form-data">
        <input type="hidden" name="meth" value="save"/>
        <table>
            <tr>
                <td align="right"><b>Width of SVG's:</b></td>
                <td><input type="text" id="sol_width" name="sol_width" value="<?=$sol_width;?>" size="10" maxlength="5">px</td>
                <td style="padding-left:20px;">The default setting for this is 600px.</td>
            </tr>
            <tr>
                <td align="right"><b>Height of SVG's:</b></td>
                <td><input type="text" id="sol_height" name="sol_height" value="<?=$sol_height;?>" size="10" maxlength="5">px</td>
                <td style="padding-left:20px;">The default setting for this is 800px.</td>
            </tr>
            <tr>
                <td colspan="3">Upgrade to remove/replace the link and logo that appears on the bottom right of every coloring book page.</td>
            </tr>
        </table>
                        <br />
        <p class="submit" style="margin-left:300px;">
            <input class="button-primary" type="submit" name="Submit" value="Save Settings" />
        </p>
    </form>
</div>
<div class="colorbook-sidebar">
    <?php
    include plugin_dir_path(__FILE__).'../includes/credits.php';
    ?>
</div>
