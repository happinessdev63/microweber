<?php only_admin_access(); ?>
<script type="text/javascript">
    mw.require('options.js');
</script>
<script type="text/javascript">
    $(document).ready(function () {
        mw.options.form('#<?php print $params['id'] ?>', function () {
            if (mw.notification != undefined) {
                mw.notification.success('Fonts updated');
            }
			
			
			if(typeof(window.parent.mw.wysiwyg) != 'undefined'){
				 
				var selected = [];
				$('#<?php print $params['id'] ?> .enabled_custom_fonts_table input:checked').each(function() {
					selected.push($(this).val());
				});

		 		window.parent.mw.wysiwyg.fontFamiliesExtended = [];
				window.parent.mw.wysiwyg.initExtendedFontFamilies(selected);
				window.parent.mw.wysiwyg.initFontSelectorBox();	
				
				var custom_fonts_stylesheet = window.parent.document.getElementById("mw-custom-user-css");
				if(custom_fonts_stylesheet != null){
					var custom_fonts_stylesheet_restyled = '<?php print api_url('template/print_custom_css') ?>?v='+Math.random(0,10000);
					custom_fonts_stylesheet.href = custom_fonts_stylesheet_restyled;

				}
				
			}
		
        });
		
		$('#<?php print $params['id'] ?> .enabled_custom_fonts_table input:checked').each(function() {
		mw_fonts_preview_load_stylesheet($(this).val());
		});
		 	
        
    });
mw_fonts_preview_loaded_stylesheets = [];
mw_fonts_preview_load_stylesheet = function(family){
         if(mw_fonts_preview_loaded_stylesheets.indexOf(family) === -1){
             mw_fonts_preview_loaded_stylesheets.push(family);
			 	
			   var filename = "//fonts.googleapis.com/css?family="+ encodeURIComponent(family)+"&text="+ encodeURIComponent(family);
			    

			   var fileref=document.createElement("link")
				fileref.setAttribute("rel", "stylesheet")
				fileref.setAttribute("type", "text/css")
				fileref.setAttribute("href", filename)
				document.getElementsByTagName("head")[0].appendChild(fileref)

					 
        }
}
	
</script>
<?php $fonts= json_decode(file_get_contents(__DIR__.DS.'fonts.json'), true); ?>
<?php if(isset($fonts['items'])): ?>
<?php $enabled_custom_fonts = get_option("enabled_custom_fonts", "template"); 

$enabled_custom_fonts_array = array();

if(is_string($enabled_custom_fonts)){
	$enabled_custom_fonts_array = explode(',',$enabled_custom_fonts);
}
 

?>

<div class="module-live-edit-settings enabled_custom_fonts_table">
  <table width="100%" cellspacing="0" cellpadding="0" class="mw-ui-table">
    <thead>
      <tr>
        <th></th>
        <th>Select Fonts</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($fonts['items'] as $font): ?>
      <tr onMouseOver="mw_fonts_preview_load_stylesheet('<?php print $font['family']; ?>')" onmouseenter="mw_fonts_preview_load_stylesheet('<?php print $font['family']; ?>')">
        <td width="30"><input type="checkbox" name="enabled_custom_fonts" <?php if(in_array($font['family'], $enabled_custom_fonts_array)): ?> checked <?php endif; ?> class="mw_option_field" option-group="template" value="<?php print $font['family']; ?>" /></td>
        <td onmouseenter="mw_fonts_preview_load_stylesheet('<?php print $font['family']; ?>')" onMouseOver="mw_fonts_preview_load_stylesheet('<?php print $font['family']; ?>')"><span style="font-size:14px; font-family:'<?php print $font['family']; ?>',sans-serif;"><?php print $font['family']; ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
