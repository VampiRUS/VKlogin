<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
class HTML_vklogin{
	static function showFields($fields){
		$count = 0;
		?>
		<form action="index.php" method="post" name="adminForm" id="adminForm">
		<div style="width:20%;float:right;">
		<div style="padding:4px;font-weight:bold;background:none repeat scroll 0 0 #F0F0F0;border-bottom:1px solid #999999;border-left:1px solid #FFFFFF;color:#666666;text-align:center;"><?php echo JText::_('COM_VKLOGIN_AVAILABLE_FIELDS');?></div>
		<span>uid</span><br/>
		<span>first_name</span><br/>
		<span>last_name</span><br/>
		<span>nickname</span><br/>
		<span>sex</span><br/>
		<span>bdate</span><br/>
		<span>city</span><br/>
		<span>country</span><br/>
		<span>timezone</span><br/>
		<span>photo</span><br/>
		<span>photo_medium</span><br/>
		<span>photo_big</span><br/>
		<span>home_phone</span><br/>
		<span>mobile_phone</span><br/>
		<span>university_name</span><br/>
		<span>faculty_name</span><br/>
		<span>graduation</span><br/>
		</div>
		<table class="adminlist" style="width:80%" cellspacing="1">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_VKLOGIN_JOMSOCIAL_FIELD');?></th>
					<th><?php echo JText::_('COM_VKLOGIN_VKONTAKTE_FIELD');?></th>
				</tr>
			</thead>
		<?php
		foreach($fields as $field){
			if($field->type == 'group'){?>
			<tr style="background-color: #EEEEEE;">
			<td colspan="2"><strong><?php echo $field->name;?></strong></td>
			</tr>
			<?php
			}else {
				?>
				<tr>
				<td><span><?php echo $field->id.':'.$field->name?></span></td>
				<td><input type="text" name="fields[<?php echo $field->id?>]" value="<?php echo $field->value?>"/></td>
				</tr>
				<?php
			}
		}
		?>
		</table>
		<div class="clr"></div>
	<input type="hidden" name="option" value="com_vklogin" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
		</form>
		<?php
	}
}

?>
