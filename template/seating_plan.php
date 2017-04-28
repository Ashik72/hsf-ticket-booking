<?php
$html = "";
foreach ($seating_plans as $key => $single_seating_plans) {
  $html .= "<div class='single_seating_plan'>";
  $html .= "<div class='single_seating_plan_title'>{$single_seating_plans['title']}</div>";
  $single_seating_plans['plan'] = explode(PHP_EOL, $single_seating_plans['plan']);

  $html .= "<div class='single_seating_plan_seats'>";

  foreach ($single_seating_plans['plan'] as $key => $single_seating_plans_row) {
    $single_seating_plans_row = explode("|", $single_seating_plans_row);
    $color_row = ( empty($single_seating_plans_row[1]) ? trim($default_seat_color) : trim($single_seating_plans_row[1]) );
    $html .= "<div class='single_row' style='color: {$color_row}'>";
    $html .= $single_seating_plans_row[0];
    $html .= "</div>";

  }

  $html .= "</div>";

  $html .= "<div class='single_seating_plan'>";

  $html .= "</div>";

}
//_e($html);
 ?>



<div class="seating_plans">

  <div class="container">
      <div class="row zone_container_general_admission_container">
          <div class="col-sm-9 zone_container">

          <?php include hsf_tb_PLUGIN_DIR.'template'.DIRECTORY_SEPARATOR.'zone_container.php'; ?>


          </div>
          <div class="col-sm-3 general_admission_container">

            <?php include hsf_tb_PLUGIN_DIR.'template'.DIRECTORY_SEPARATOR.'general_admission.php'; ?>


          </div>
      </div>
  </div>

  <div class="container">
  <div class="row">
      <div class="col-sm-9 zone_container_raised">
        <?php include hsf_tb_PLUGIN_DIR.'template'.DIRECTORY_SEPARATOR.'raised_seating.php'; ?>
      </div>
  </div>
</div>

</div>
<?php include hsf_tb_PLUGIN_DIR.'template'.DIRECTORY_SEPARATOR.'seating_opts.php'; ?>
