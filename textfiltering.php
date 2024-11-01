<?php
/**
 * Plugin Name: Text Filtering
 * Description: Filter out the text in your posts, replace them with your preferred phrase across all your posts or for the specified category posts.
 * Version:     2.1.0
 * Author:      Arun Thomas
 * Text Domain: text-filtering
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Add settings link on plugin page
function textfiltering_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=textfiltering.php">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'textfiltering_settings_link' );

function textfiltering_filter_content($content){
  $category = get_the_category();
  $category_array = [];

  foreach($category as $key => $cat){
    $category_array[$key] = $category[$key]->cat_name;
  }

  $filters_count = sizeof(get_option('textfiltering_option_3_name'));
  $replace_text_array = [];

  foreach(get_option('textfiltering_option_2_name') as $key => $replace_text){
    $replace_text_array[$key] = $replace_text;
  }

  for($i=0;$i<$filters_count;$i++){
    if( ((in_array(get_option('textfiltering_option_3_name')[$i], $category_array))||(get_option('textfiltering_option_3_name')[$i] == 'All')) & (get_option('textfiltering_option_enable')[$i] == 1) ){
    	$content = str_ireplace(get_option('textfiltering_option_name')[$i], get_option('textfiltering_option_2_name')[$i], $content);
    }
  }

	return $content;
}
add_filter( 'the_content', 'textfiltering_filter_content' );

function textfiltering_register_settings() {
   add_option( 'textfiltering_option_name', ['Devil']);
   add_option( 'textfiltering_option_2_name', ['*text muted*']);
   add_option( 'textfiltering_option_3_name', ['All']);
   add_option( 'textfiltering_option_enable', [1]);

   register_setting( 'textfiltering_options_group', 'textfiltering_option_name', 'textfiltering_callback' );
   register_setting( 'textfiltering_options_group', 'textfiltering_option_2_name', 'textfiltering_callback' );
   register_setting( 'textfiltering_options_group', 'textfiltering_option_3_name', 'textfiltering_callback' );
   register_setting( 'textfiltering_options_group', 'textfiltering_option_enable', 'textfiltering_callback' );
}
add_action( 'admin_init', 'textfiltering_register_settings' );

function textfiltering_register_options_page() {
  add_options_page('Text Filtering Settings', 'Text Filtering', 'manage_options', 'textfiltering', 'textfiltering_options_page');
}
add_action('admin_menu', 'textfiltering_register_options_page');

function textfiltering_options_page()
{
?>
  <style type="text/css">
    table {
      width: 100%;
      background: #fff;
      -moz-border-radius: 3px;
      -webkit-border-radius: 3px;
      border-radius: 3px;
      border: 1px solid #ddd;
      margin:auto;
    }

    table thead, table tfoot { background: #f5f5f5; }
    table thead tr th,
    table tfoot tr th,
    table tbody tr td,
    table tr td,
    table tfoot tr td { font-size: 12px; line-height: 18px; text-align: left; }
    table thead tr th,
    table tfoot tr td { padding: 8px 10px 9px; font-size: 14px; font-weight: bold; color: #222; }
    table thead tr th:first-child, table tfoot tr td:first-child { border-left: none; }
    table thead tr th:last-child, table tfoot tr td:last-child { border-right: none; }

    table tbody tr.even,
    table tbody tr.alt { background: #f9f9f9; }
    table tbody tr:nth-child(even) { background: #f9f9f9; }
    table tbody tr td { color: #333; padding: 9px 10px; vertical-align: top; border: none; }

    tr td input, tr td select{
      width: 100%;
    }
    tr td{
      padding: 0px !important;
    }
  </style>
  <div>
    <?php 
      screen_icon(); 
      $filters_count = sizeof(get_option('textfiltering_option_3_name'));
    ?>

    <script type="text/javascript">
      filters_count = <?php echo $filters_count; ?>
    </script>

    <h1>Text Filtering</h1>
    <form method="post" action="options.php" onsubmit="validate_textfiltering_settings()">
      <?php settings_fields( 'textfiltering_options_group' ); ?>
      <p>Specify your text filtering <a class="button button-primary" onclick="textfiltering_addfilter()"><span class="dashicons dashicons-plus" style="vertical-align: middle"></span> Add Filter</a></p>
      <div class="wp-block-table">
        <table class="table table-striped">
          <thead>
            <tr valign="top">
              <th scope="row">
                <label for="textfiltering_option_name">Text to be searched in posts</label>
              </th>
              <th scope="row">
                <label for="textfiltering_option_2_name">Text to be replaced with</label>
              </th>
              <th scope="row">
                <label for="textfiltering_option_3_name">Category</label>
              </th>
              <th scope="row">
                <label for="textfiltering_option_enable">Enable</label>
              </th>
              <th>
                Action
              </th>
            </tr>
          </thead>
          <tbody id="textfiltering-filter-block">
            <?php
            for($i=0;$i<$filters_count;$i++){
            ?>
            <tr valign="top" id="textfiltering-filter-<?php echo $i; ?>">
              <td>
                <input type="text" id="textfiltering_option_name" name="textfiltering_option_name[]" value="<?php echo get_option('textfiltering_option_name')[$i]; ?>" required />
              </td>
              <td>
                <input type="text" id="textfiltering_option_2_name" name="textfiltering_option_2_name[]" value="<?php echo get_option('textfiltering_option_2_name')[$i]; ?>" required />
              </td>
              <td>
                <select id="textfiltering_option_3_name" name="textfiltering_option_3_name[]" value="<?php echo get_option('textfiltering_option_3_name')[$i]; ?>">
                    <option>All</option>
                  <?php
                  $cats = get_categories();
                  foreach($cats as $cat){
                  ?>
                    <option <?php if(get_option('textfiltering_option_3_name')[$i] == $cat->cat_name){ echo "selected"; } ?>><?php echo $cat->cat_name ?></option>
                <?php } ?>
                </select>
              </td>
              <td style="text-align: center">
                <input class="enable-checkbox" type="checkbox" name="textfiltering_option_enable[]" value="1" width="100%" <?php if(get_option('textfiltering_option_enable')[$i] == 1){ echo "checked"; } ?> />
                <input class="enable-checkbox-hidden" type="hidden" name="textfiltering_option_enable[]" value="0" />
              </td>
              <td>
                <a class="button" style="width: 100%; text-align: center" onclick="textfiltering_removefilter(<?php echo $i ?>)"><span class="dashicons dashicons-trash" style="vertical-align: middle"></span> Remove</a>
              </td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
      <?php submit_button(); ?>
    </form>
  </div>
  
  <script type="text/javascript">
    function textfiltering_removefilter(i){
      var element = document.getElementById('textfiltering-filter-'+i);
      element.parentNode.removeChild(element);
    }

    function validate_textfiltering_settings(){
      var checkboxes = document.getElementsByClassName("enable-checkbox");
      var checkboxes_hidden = document.getElementsByClassName("enable-checkbox-hidden");
      for (var i = 0; i < checkboxes.length; i++) {
        if( (checkboxes.item(i)).checked ) {
          (checkboxes_hidden.item(i)).disabled = true;
        }
      }
    }

    function textfiltering_addfilter(){
      var element = document.getElementById('textfiltering-filter-block');
      current_filters_html = element.innerHTML;
      new_filter_html = '<tr valign="top" id="textfiltering-filter-'+filters_count+'">'
              +'<td>'
                +'<input type="text" id="textfiltering_option_name" name="textfiltering_option_name[]" required />'
              +'</td>'
              +'<td>'
                +'<input type="text" id="textfiltering_option_2_name" name="textfiltering_option_2_name[]" required />'
              +'</td>'
              +'<td>'
                +'<select id="textfiltering_option_3_name" name="textfiltering_option_3_name[]">'
                    +'<option>All</option>';
                  <?php
                  $cats = get_categories();
                  foreach($cats as $cat){
                  ?>
                    new_filter_html+='<option><?php echo $cat->cat_name ?></option>';
                <?php } ?>
                new_filter_html+='</select>'
              +'</td>'
              +'<td style="text-align: center"><input class="enable-checkbox" type="checkbox" name="textfiltering_option_enable[]" value="1" width="100%" checked /><input class="enable-checkbox-hidden" type="hidden" name="textfiltering_option_enable[]" value="0" checked /></td>'
              +'<td>'
                +'<a class="button" style="width: 100%; text-align: center" onclick="textfiltering_removefilter('+filters_count+')"><span class="dashicons dashicons-trash" style="vertical-align: middle"></span> Remove</a>'
              +'</td>'
            +'</tr>';
      element.innerHTML = current_filters_html+new_filter_html;
      filters_count++;
    }
  </script>
<?php
} 
?>