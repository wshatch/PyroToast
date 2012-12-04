<?php if($module_tests) : ?>
  <table border="0" class="table-list"> 
	<thead>
    <tr>
		  <th width="20">
			  <?php echo form_checkbox(array('name' => 'action_to_all', 'class' => 'check-all')) ?>
			</th>
			<th class="collapse">
			  <?php echo lang('pyrotoast:module_name') ?>
			</th>
			<th class="collapse">
			  <?php echo lang('pyrotoast:test_class') ?>
			</th>
			<th>
			  <?php echo lang('pyrotoast:test_method') ?>
			</th>
		</tr>
	</thead>
	<tbody>
  <?php foreach($module_tests as $module): ?>
    <?php foreach($module['tests'] as $test): ?>
      <tr>
		    <td> <?php echo form_checkbox('action_to[]', $module['name'].':'.$test['class'] .':' . $test['method']); ?></td>
  			<td class="collapse"><?php echo $module['name'] ?> </td>
            <td class="collapse"><?php echo $test['class'] ?> </td>
  			<td><?php echo $test['method'] ?> </td>
  		</tr>
	<?php endforeach; ?>
   <?php endforeach; ?>
     
	</tbody>
	</table>
<?php else: ?>
 <div class="no_data"> <?php echo lang("pyrotoast:no_testable_modules"); ?> </div>
<?php endif ?>
