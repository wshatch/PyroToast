<section class="title">
  <h4> <?php echo lang('pyrotoast:report')?> </h4>
</section>
<section class="item">
  <?php if($fails !== 0): ?>
  <h4 class="fail"> Failure </h4>
  <?php else: ?>
   <h4 class="success"> All tests passed! </h4>
   <?php endif; ?>
</section>
<section class="item">
<?php foreach($test_results as $class => $data): ?>
  <h4> <?php $class ?> </h4>
  <table>
    <tr>
      <th>
        <?php echo lang('pyrotoast:test_method') ?>
      </th>
      <th>
        <?php echo lang('pyrotoast:status') ?>
      </tr>
    </tr>
  <?php foreach($data['results'] as $result): ?>
    <tr>
      <td><?php echo $result['method']; ?></td>
      <td class="<?php echo $result ?>"><?php echo $result['Result'] ?></td>
    </tr>
  <?php endforeach; ?>
<?php endforeach; ?>
  </table>
</section>
