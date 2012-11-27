<fieldset id="filters">
  <legend> <?php echo lang('global:filters'); ?> </legend>
    
    <?php echo form_open(''); ?>
    <?php echo form_hidden('f_module', $module_details['slug']); ?>
    <ul>  
        <li>
                <?php echo lang('pyrotoast:modules_label', 'f_module'); ?>
                <?php echo form_dropdown('f_module', $module_names); ?>
        </li>
        <li>

                <?php echo lang('pyrotoast:class_label', 'f_module'); ?>
                <?php echo form_dropdown('f_module', $class_names); ?>
        </li>
    </ul>
    <?php echo form_close(); ?>

</fieldset>
