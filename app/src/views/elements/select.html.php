<?
/* @var $p \Ophp\ViewPrinter */
/* @var $field \Ophp\FormField */
?>
<select name="<? $p($field->getName())->attrVal() ?>">
	<? foreach ($field->getOptions() as $option) : ?>
		<option 
			<? $p($option->isDisabled() ? 'disabled="disabled"' : '')->html(); ?>
			<? $p($option->value === $field->getValue() ? 'selected="selected"' : '')->html(); ?>
			value="<? $p($option->value)->attrVal() ?>">
			<? $p($option->label)->chData() ?>
		</option>
	<? endforeach; ?>
</select>
