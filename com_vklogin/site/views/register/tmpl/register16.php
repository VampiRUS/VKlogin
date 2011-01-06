<?php // no direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.mootools');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

?>

<?php
	if(isset($this->message)){
		$this->display('message');
	}
?>
<script>
function showbox(){
	document.getElementById('addition_form').style.display='block';
	return false;
}
</script>

<div class="registration<?php echo $this->params->get('pageclass_sfx')?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php endif; ?>

	<form id="member-registration" action="<?php echo JRoute::_( 'index.php?option=com_vklogin' ); ?>" method="post" class="form-validate">
<?php foreach ($this->form->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($fieldset->name);?>
	<?php if (count($fields)):?>
		<fieldset>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
			<legend><?php echo JText::_($fieldset->label);?></legend>
		<?php endif;?>
			<dl>
		<?php foreach($fields as $field):// Iterate through the fields in the set and display them.?>
			<?php if ($field->hidden):// If the field is hidden, just display the input.?>
				<?php echo $field->input;?>
			<?php else:?>
				<dt>
				<?php echo $field->label; ?>
				<?php if (!$field->required): ?>
					<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL');?></span>
				<?php endif; ?>
				</dt>
				<dd><?php echo $field->input;?></dd>
			<?php endif;?>
		<?php endforeach;?>
			</dl>
		</fieldset>
	<?php endif;?>
<?php endforeach;?>

		<button type="submit" class="validate"><?php echo JText::_('JREGISTER');?></button>
		<div>
			<?php echo JHtml::_('form.token');?>
		</div>
	</form>
</div>
<a href="#" onclick="return showbox()"><?php echo JText::_('COM_VKLOGIN_I\'M_ALREADY_REGISTRED');?></a>
<div id="addition_form" style="display:none;">
<form action="<?php echo JRoute::_( 'index.php?option=com_vklogin' ); ?>" method="post" class="form-validate">
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
<tr>
	<td width="30%" height="40">
		<label id="usermsg" for="user">
			<?php echo JText::_( 'COM_VKLOGIN_SITE_USER_NAME' ); ?>:
		</label>
	</td>
  	<td>
  		<input type="text" name="username" id="user" size="40" value="" class="inputbox required" maxlength="50" /> *
  	</td>
</tr>
<tr>
	<td width="30%" height="40">
		<label id="passwordmsg" for="password">
			<?php echo JText::_( 'COM_VKLOGIN_SITE_PASSWORD' ); ?>:
		</label>
	</td>
  	<td>
  		<input type="password" id="password" name="password" size="40" value="" class="inputbox required" maxlength="50" /> *
  	</td>
</tr>
</table>
<button class="button validate" type="submit"><?php echo JText::_('COM_VKLOGIN_MERGE'); ?></button>
<input type="hidden" name="task" value="connect"/>
</form>
</div>
