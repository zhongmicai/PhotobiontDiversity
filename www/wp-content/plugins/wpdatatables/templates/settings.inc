<div id="wdtPreloadLayer">
	<div style="position: absolute; top: 50%; left: 50%; margin-left: -208px; margin-top: -13px; text-align: center">
		<img src="<?php echo plugins_url(); ?>/wpdatatables/assets/img/loadingAnimation.gif" />
	</div>
</div>
<div class="wrap">
    <div id="icon-tools" class="icon32"><br></div>
    <h2>wpDataTables settings</h2>
<form method="post" action="<?php echo PDT_ROOT_URL ?>" id="wpDataTablesSettings">
	<h3>Main settings</h3>
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row">
                <label for="wpUseSeparateCon">Use separate MySQL connection</label>
            </th>
            <td>
                <input type="checkbox" id="wpUseSeparateCon" name="wpUseSeparateCon" <?php if($wpUseSeparateCon) { ?>checked="checked"<?php } ?> />
                <span class="description">If this checkbox is checked, wpDataTables will use its own connection to MySQL bases. In other case it will use the main WordPress MySQL connection.</span>
            </td>
        </tr>
        <tr class="wpMySQLParam">
            <th scope="row">
                <label for="wpMySqlHost">MySQL host</label>
            </th>
            <td>
                <input type="text" value="<?php echo $wpMySqlHost ?>" name="wpMySqlHost" id="wpMySqlHost" />
                <span class="description">MySQL host address.</span>
            </td>
        </tr>
        <tr class="wpMySQLParam">
            <th scope="row">
                <label for="wpMySqlDB">MySQL database</label>
            </th>
            <td>
                <input type="text" value="<?php echo $wpMySqlDB ?>" name="wpMySqlDB" id="wpMySqlDB" />
                <span class="description">MySQL database name.</span>
            </td>
        </tr>
        <tr class="wpMySQLParam">
            <th scope="row">
                <label for="wpMySqlUser">MySQL user</label>
            </th>
            <td>
                <input type="text" value="<?php echo $wpMySqlUser ?>" name="wpMySqlUser" id="wpMySqlUser" />
                <span class="description">MySQL username for the connection.</span>
            </td>
        </tr>
        <tr class="wpMySQLParam">
            <th scope="row">
                <label for="wpMySqlPwd">MySQL password</label>
            </th>
            <td>
                <input type="password" value="<?php echo $wpMySqlPwd ?>" name="wpMySqlPwd" id="wpMySqlPwd" />
                <span class="description">MySQL password for the provided user.</span>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="wpInterfaceLanguage">Interface language</label>
            </th>
            <td>
                <select name="wpInterfaceLanguage" id="wpInterfaceLanguage">
                    <option value="" <?php if($wpInterfaceLanguage == '') {?>selected="selected"<?php } ?> >English (default)</option>
                    <?php foreach($languages as $language) { ?>
                    <option value="<?php echo $language['file']?>" <?php if($wpInterfaceLanguage == $language['file']) {?>selected="selected"<?php } ?> >
						<?php echo $language['name']; ?>
					</option>
                    <?php } ?>
                </select>
                <span class="description">Pick the language which will be used in tables interface.</span>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="wpRenderCharts">Render charts</label>
            </th>
            <td>
                <select name="wpRenderCharts" id="wpRenderCharts">
                    <option value="above" <?php if($wpRenderCharts == 'above') {?>selected="selected"<?php } ?> >Above the table</option>
                    <option value="below" <?php if($wpRenderCharts == 'below') {?>selected="selected"<?php } ?> >Below the table</option>
                </select>
                <span class="description">Choose where you would like to render the charts.</span>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="wpRenderFilter">Render advanced filter</label>
            </th>
            <td>
                <select name="wpRenderFilter" id="wpRenderFilter">
                    <option value="header" <?php if($wpRenderFilter == 'header') {?>selected="selected"<?php } ?> >In the header</option>
                    <option value="footer" <?php if($wpRenderFilter == 'footer') {?>selected="selected"<?php } ?> >In the footer</option>
                </select>
                <span class="description">Choose where you would like to render the advanced filter for tables where enabled.</span>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="wpDateFormat">Date format</label>
            </th>
            <td>
                <select name="wpDateFormat" id="wpDateFormat">
                    <option value="d/m/Y" <?php if($wpDateFormat == 'd/m/Y') {?>selected="selected"<?php } ?> >15/07/2005</option>
                    <option value="d.m.Y" <?php if($wpDateFormat == 'd.m.Y') {?>selected="selected"<?php } ?> >15.07.2005</option>
                    <option value="d.m.y" <?php if($wpDateFormat == 'd.m.y') {?>selected="selected"<?php } ?> >15.07.05</option>
                    <option value="d M Y" <?php if($wpDateFormat == 'd M Y') {?>selected="selected"<?php } ?> >15 Jun 2005</option>
                </select>
                <span class="description">Pick the date format to use in date column type.</span>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="wpTopOffset">Top offset in pixels</label>
            </th>
            <td>
                <input type="text" value="<?php echo $wpTopOffset ?>" name="wpTopOffset" id="wpTopOffset" />
                <span class="description">Put the top offset in pixels for the "Fixed columns" feature, to adjust to your template.</span>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="wpLeftOffset">Left offset in pixels</label>
            </th>
            <td>
                <input type="text" value="<?php echo $wpLeftOffset ?>" name="wpLeftOffset" id="wpLeftOffset" />
                <span class="description">Put the left offset in pixels for the "Fixed columns" feature, to adjust to your template.</span>
            </td>
        </tr>     
        <tr>
            <td colspan="2">
                <input type="submit" name="submit" id="submit" class="button-primary" value="Save options">
            </td> 
        </tr>                    
        </tbody>
    </table>
    <h3>Color and font settings</h3>
    <p>Leave the setting blank to use default value.</p>
    <table class="form-table colorFontSettings">
        <tbody>
        <tr valign="top">
            <th scope="row">
                <label for="wdtHeaderBaseColor">Header base color</label>
            </th>
            <td>
            	<input type="text" name="wdtHeaderBaseColor" id="wdtHeaderBaseColor" value="<?php echo (!empty($wdtFontColorSettings['wdtHeaderBaseColor']) ? $wdtFontColorSettings['wdtHeaderBaseColor'] : '')?>" />
            	<span class="description">The color is used to create a gradient on the table header.</span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="wdtHeaderHoverColor">Header active and hover color</label>
            </th>
            <td>
            	<input type="text" name="wdtHeaderActiveColor" id="wdtHeaderActiveColor" value="<?php echo (!empty($wdtFontColorSettings['wdtHeaderActiveColor']) ? $wdtFontColorSettings['wdtHeaderActiveColor'] : '')?>" />
            	<span class="description">This color is used when you hover the mouse above the table header, or when you choose a column.</span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="wdtHeaderFontColor">Header font color</label>
            </th>
            <td>
            	<input type="text" name="wdtHeaderFontColor" id="wdtHeaderFontColor" value="<?php echo (!empty($wdtFontColorSettings['wdtHeaderFontColor']) ? $wdtFontColorSettings['wdtHeaderFontColor'] : '')?>" />
            	<span class="description">This color is used for the font in the table header.</span>
            </td>
        </tr>        
        <tr valign="top">
            <th scope="row">
                <label for="wdtHeaderBorderColor">Header border color</label>
            </th>
            <td>
            	<input type="text" name="wdtHeaderBorderColor" id="wdtHeaderBorderColor" value="<?php echo (!empty($wdtFontColorSettings['wdtHeaderBorderColor']) ? $wdtFontColorSettings['wdtHeaderBorderColor'] : '')?>" />
            	<span class="description">This color is used for the border in the table header.</span>
            </td>
        </tr>     
        <tr valign="top">
            <th scope="row">
                <label for="wdtEvenRowColor">Even row background color</label>
            </th>
            <td>
            	<input type="text" name="wdtEvenRowColor" id="wdtEvenRowColor" value="<?php echo (!empty($wdtFontColorSettings['wdtEvenRowColor']) ? $wdtFontColorSettings['wdtEvenRowColor'] : '')?>" />
            	<span class="description">This color is used for for background in even rows.</span>
            </td>
        </tr>           
        <tr valign="top">
            <th scope="row">
                <label for="wdtOddRowColor">Odd row background color</label>
            </th>
            <td>
            	<input type="text" name="wdtOddRowColor" id="wdtOddRowColor" value="<?php echo (!empty($wdtFontColorSettings['wdtOddRowColor']) ? $wdtFontColorSettings['wdtOddRowColor'] : '')?>" />
            	<span class="description">This color is used for for background in odd rows.</span>
            </td>
        </tr>           
        <tr valign="top">
            <th scope="row">
                <label for="wdtHoverRowColor">Hover row color</label>
            </th>
            <td>
            	<input type="text" name="wdtHoverRowColor" id="wdtHoverRowColor" value="<?php echo (!empty($wdtFontColorSettings['wdtHoverRowColor']) ? $wdtFontColorSettings['wdtHoverRowColor'] : '')?>" />
            	<span class="description">This color is used for to highlight the row when you hover your mouse above it.</span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="wdtActiveEvenCellColor">Cell color in active (sorted) columns for even rows</label>
            </th>
            <td>
            	<input type="text" name="wdtActiveEvenCellColor" id="wdtActiveEvenCellColor" value="<?php echo (!empty($wdtFontColorSettings['wdtActiveEvenCellColor']) ? $wdtFontColorSettings['wdtActiveEvenCellColor'] : '')?>" />
            	<span class="description">This color is used for background in cells which are in the active columns (columns used for sorting) in even rows.</span>
            </td>
        </tr>         
        <tr valign="top">
            <th scope="row">
                <label for="wdtActiveOddCellColor">Cell color in active (sorted) columns for odd rows</label>
            </th>
            <td>
            	<input type="text" name="wdtActiveOddCellColor" id="wdtActiveOddCellColor" value="<?php echo (!empty($wdtFontColorSettings['wdtActiveOddCellColor']) ? $wdtFontColorSettings['wdtActiveOddCellColor'] : '')?>" />
            	<span class="description">This color is used for background in cells which are in the active columns (columns used for sorting) in odd rows.</span>
            </td>
        </tr>         
        <tr valign="top">
            <th scope="row">
                <label for="wdtTableOuterBorderColor">Table outer border color</label>
            </th>
            <td>
            	<input type="text" name="wdtTableOuterBorderColor" id="wdtTableOuterBorderColor" value="<?php echo (!empty($wdtFontColorSettings['wdtTableOuterBorderColor']) ? $wdtFontColorSettings['wdtTableOuterBorderColor'] : '')?>"  />
            	<span class="description">This color is used for the outer border of the whole table body.</span>
            </td>
        </tr>           
        <tr valign="top">
            <th scope="row">
                <label for="wdtTableInnerBorderColor">Table inner border color</label>
            </th>
            <td>
            	<input type="text" name="wdtTableInnerBorderColor" id="wdtTableInnerBorderColor" value="<?php echo (!empty($wdtFontColorSettings['wdtTableInnerBorderColor']) ? $wdtFontColorSettings['wdtTableOuterBorderColor'] : '')?>"  />
            	<span class="description">This color is used for the inner border in the table between cells.</span>
            </td>
        </tr>           
        <tr valign="top">
            <th scope="row">
                <label for="wdtTableFontColor">Table font color</label>
            </th>
            <td>
            	<input type="text" name="wdtTableFontColor" id="wdtTableFontColor" value="<?php echo (!empty($wdtFontColorSettings['wdtTableFontColor']) ? $wdtFontColorSettings['wdtTableFontColor'] : '')?>" />
            	<span class="description">This color is used for the main font in table cells.</span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="wdtTableFont">Table font</label>
            </th>
            <td>
            	<select name="wdtTableFont" id="wdtTableFont" style="width: 200px;">
            		<option value="" <?php echo (empty($wdtFontColorSettings['wdtTableFont']) ? 'selected="selected"' : '')?> ></option>
            		<?php foreach($wdtFonts as $font) { ?>
            			<option value="<?php echo $font ?>" <?php echo ((!empty($wdtFontColorSettings['wdtTableFont']) && ($wdtFontColorSettings['wdtTableFont'] == $font)) ? 'selected="selected"' : '')?>><?php echo $font ?></option>
            		<?php } ?>
            	</select>
            	<span class="description">This font will be used in rendered tables. Leave blank not to override default theme settings.</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="submit" name="submit" id="submit" class="button-primary" value="Save options">
            	<button class="resetColorSettings button-primary">Reset colors and fonts to default</button>
            </td> 
        </tr> 
        </tbody>      
    </table>    
</form>
</div>
<style>
    tr.wpMySQLParam {
        display: none;
    }
    #wdtPreloadLayer {
    	position: absolute;
    	height: 100%;
    	width: 100%;
    	background-color:rgba(255,255,255,0.7);
    	text-align: center;
    	display: none;
    }    
</style>
<script type="text/javascript">
jQuery(document).ready(function(){
	// exColorPicker
	jQuery('#wdtHeaderBaseColor').modcoder_excolor();
	jQuery('#wdtHeaderActiveColor').modcoder_excolor();
	jQuery('#wdtHeaderFontColor').modcoder_excolor();
	jQuery('#wdtHeaderBorderColor').modcoder_excolor();
	jQuery('#wdtTableOuterBorderColor').modcoder_excolor();
	jQuery('#wdtTableInnerBorderColor').modcoder_excolor();
	jQuery('#wdtTableFontColor').modcoder_excolor();
	jQuery('#wdtHoverRowColor').modcoder_excolor();
	jQuery('#wdtOddRowColor').modcoder_excolor();
	jQuery('#wdtEvenRowColor').modcoder_excolor();
	jQuery('#wdtActiveOddCellColor').modcoder_excolor();
	jQuery('#wdtActiveEvenCellColor').modcoder_excolor();
	
	jQuery('.resetColorSettings').click(function(e){
		e.preventDefault();
		jQuery('table.colorFontSettings input[type="text"]').val('').change();
		jQuery('table.colorFontSettings select option[value=""]').attr('selected','selected');
		jQuery('#wpDataTablesSettings').submit();
	});
	
    if(jQuery('#wpUseSeparateCon').is(':checked')){
        jQuery('tr.wpMySQLParam').show();
    }

    jQuery('#wpUseSeparateCon').change(function(){
        jQuery('tr.wpMySQLParam').toggle();
    });
    
    jQuery('#wpDataTablesSettings').submit(function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        var data = {
            action: 'wdt_save_settings',
            wpUseSeparateCon: (jQuery('#wpUseSeparateCon').attr('checked') == 'checked'),
            wpMySqlHost: jQuery('#wpMySqlHost').val(),
            wpMySqlDB: jQuery('#wpMySqlDB').val(),
            wpMySqlUser: jQuery('#wpMySqlUser').val(),
            wpMySqlPwd: jQuery('#wpMySqlPwd').val(),
            wpRenderCharts: jQuery('#wpRenderCharts').val(),
            wpRenderFilter: jQuery('#wpRenderFilter').val(),
            wpInterfaceLanguage: jQuery('#wpInterfaceLanguage').val(),
            wpDateFormat: jQuery('#wpDateFormat').val(),
            wpTopOffset: jQuery('#wpTopOffset').val(),
            wpLeftOffset: jQuery('#wpLeftOffset').val(),
            wdtHeaderBaseColor: jQuery('#wdtHeaderBaseColor').val(),
            wdtHeaderActiveColor: jQuery('#wdtHeaderActiveColor').val(),
            wdtHeaderFontColor: jQuery('#wdtHeaderFontColor').val(),
            wdtHeaderBorderColor: jQuery('#wdtHeaderBorderColor').val(),
            wdtTableOuterBorderColor: jQuery('#wdtTableOuterBorderColor').val(),
            wdtTableInnerBorderColor: jQuery('#wdtTableInnerBorderColor').val(),
            wdtTableFontColor: jQuery('#wdtTableFontColor').val(),
            wdtTableFont: jQuery('#wdtTableFont').val(),
            wdtHoverRowColor: jQuery('#wdtHoverRowColor').val(),
            wdtOddRowColor: jQuery('#wdtOddRowColor').val(),
            wdtEvenRowColor: jQuery('#wdtEvenRowColor').val(),
            wdtActiveOddCellColor: jQuery('#wdtActiveOddCellColor').val(),
            wdtActiveEvenCellColor: jQuery('#wdtActiveEvenCellColor').val()
        };
        jQuery('#wdtPreloadLayer').show();
        jQuery.post(ajaxurl, data, function(response) {
   	        jQuery('#wdtPreloadLayer').hide();
            if(response=='0'){
                wdtAlertDialog('Settings saved successfully','Success!');
            }else{
                wdtAlertDialog('There was a problem saving your settings','Error!');
            }
        });
    });
});

function wdtAlertDialog(str, title){
        	var alert_dialog_str = '<div id="alertDialog" title="'+title+'">';
        	alert_dialog_str += str;
        	alert_dialog_str += '</div>';
        	jQuery(alert_dialog_str).dialog({
        		modal: true,
        		buttons: {
        			'OK': function(){
        				jQuery(this).dialog('close');
        				jQuery('#alertDialog').remove();
        			}
        		}
        	});
            return;
}
</script>
