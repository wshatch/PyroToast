<section class="title">
  <h4> <?php echo lang('pyrotoast:module_title'); ?> </h4>
</section>

<section class="item">
  <?php template_partial('filters'); ?>
  <?php echo form_open('admin/pyrotoast/run_tests'); ?>
  <div id="filter_stage">
    <?php template_partial('tests'); ?>
  </div>  
  <div class="table_action_buttons">
    <?php $this->load->view('admin/partials/buttons', array('buttons' => array('activate'))); ?>
  </div>
  <?php echo form_close(); ?>
</section>
