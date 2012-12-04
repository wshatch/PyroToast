<fieldset id="filters">
  <legend> <?php echo lang('global:filters'); ?> </legend>
    
    <?php echo form_open(''); ?>
    <?php echo form_hidden('f_module', $module_details['slug']); ?>
    <ul>  
        <li>
                <?php echo lang('pyrotoast:module_name', 'f_module_name'); ?>
                <?php echo form_dropdown('f_module_name', array(0 => lang('global:select-all')) + $module_names); ?>
        </li>
        <li>

                <?php echo lang('pyrotoast:test_class', 'f_classes'); ?>
                <?php echo form_dropdown('f_classes', array(0 => lang('global:select-all')) +$class_names); ?>
        </li>
    </ul>
    <?php echo form_close(); ?>

</fieldset>
