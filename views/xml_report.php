<?php print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" ?>
<testsuites>
 
  <?php 
  if($results){
  foreach($results as $class => $data): ?>
    <testsuite name="<?php echo $data;?>"
    >
    <?php foreach($data['results'] as $result): ?>
      <testcase
                name="<?php echo $result['method']; ?>"
                file="<?php echo $result['File Name'];?>"
                line="<?php echo $result['Line Number'];?>"
                >
        <?php if ($result['Result'] === "Failed") :?>
            <failure type="<?php echo $result['Test Datatype'] ?>" message="<?php echo $result['Notes'] ?>" />
        <?php endif;?>
      </testcase>
    <?php endforeach; ?>
    </testsuite>
  <?php endforeach;} ?>

</testsuites>
