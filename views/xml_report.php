<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>" ?>
<testsuites>
  <?php foreach($test_results as $class => $data): ?>
    <testsuite name="<?php echo $class ?>"
    >
    <?php foreach($data['results'] as $result): ?>
      <testcase name="<?php $result['method']; ?>"
                file=""
                line=""
                assertions=""
                time=""
    <?php endforeach; ?>
  <?php endforeach; ?>

</testsuites>
