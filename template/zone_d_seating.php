<div class="zone_d_seating">


  <?php
  $z_b_seating_plan = $seating_plans['zone_d'];
  $z_b_seating_plan_title = $z_b_seating_plan['title'];

   ?>



  <div class="seating_z_d">

    <?php
    $z_b_seating_plan_data = explode(PHP_EOL, $z_b_seating_plan['plan_data']);

    $z_b_seating_plan = explode(PHP_EOL, $z_b_seating_plan['plan']);
    //$z_b_seating_plan = array_map('intval', $z_b_seating_plan);
    $z_b_seating_plan = ( (is_array($z_b_seating_plan)) ? $z_b_seating_plan : [] );

    $default_seat_color = trim($default_seat_color);
    $html = "";

    $width = (100/$z_b_seating_max_count) - 1;
    $width = 15;
    $int_data_count = 0;

    foreach ($z_b_seating_plan as $key => $z_b_seat) {
      $z_b_seat = explode("|", $z_b_seat);

      $seat_number = 0;

      $z_b_seating_plan_data_single = $z_b_seating_plan_data[$int_data_count];
$int_data_count++;
      $z_b_seating_plan_data_single = (empty($z_b_seating_plan_data_single) ? "" : explode("|", $z_b_seating_plan_data_single)) ;
      if (count($z_b_seat) == 1) {

        $z_b_seat = (int) $z_b_seat[0];

        $html .= "<div class='z_d_seat_holder'>";
        while ($z_b_seat > 0) {

          $uid_string = str_replace(' ', '-', $z_b_seating_plan_data_single[0]); // Replaces all spaces with hyphens.
          $uid_string = preg_replace('/[^A-Za-z0-9\-]/', '', $uid_string); // Removes special chars.
          $uid = $uid_string.((int) $z_b_seating_plan_data_single[1])."_".$z_b_seat;

          $seat_number++;
          //$html .= "<span class='z_d_seat {$uid}' data-uid='{$uid}' data-seat_number='{$seat_number}' data-price='{$z_b_seating_plan_data_single[1]}' data-name='{$z_b_seating_plan_data_single[0]}' style='width: {$width}%'><img src='".$default_seat_color."'></span>";
          $html .= "<span class='z_d_seat {$uid}' data-uid='{$uid}' data-seat_number='{$seat_number}' data-price='{$z_b_seating_plan_data_single[1]}' data-name='{$z_b_seating_plan_data_single[0]}' style='width: {$width}%'><img style='width: {$width}px; margin: 1px; padding: 0' src='".$default_seat_color."'></span>";


          $z_b_seat--;
        }
        $html .= "</div>";

        continue;
      }

      $z_b_seat_no = (int) $z_b_seat[0];
      $z_b_seat_color = $z_b_seat[1];
      $seat_number = 0;

      $html .= "<div class='z_d_seat_holder'>";
      while ($z_b_seat_no > 0) {

        $seat_number++;
        $uid_string = str_replace(' ', '-', $z_b_seating_plan_data_single[0]); // Replaces all spaces with hyphens.
        $uid_string = preg_replace('/[^A-Za-z0-9\-]/', '', $uid_string); // Removes special chars.
        $uid = $uid_string.((int) $z_b_seating_plan_data_single[1])."_".$z_b_seat_no;

        //$html .= "<span class='z_d_seat {$uid}' data-uid='{$uid}' data-seat_number='{$seat_number}' data-price='{$z_b_seating_plan_data_single[1]}' data-name='{$z_b_seating_plan_data_single[0]}' style='width: {$width}%'><img src='".$z_b_seat_color."'></span>";
        $html .= "<span class='z_d_seat {$uid}' data-uid='{$uid}' data-seat_number='{$seat_number}' data-price='{$z_b_seating_plan_data_single[1]}' data-name='{$z_b_seating_plan_data_single[0]}' style='width: {$width}%'><img style='width: {$width}px; margin: 1px; padding: 0' src='".$z_b_seat_color."'></span>";

        $z_b_seat_no--;
      }
      $html .= "</div>";


    }

    _e($html);

     ?>

     <div class="title_z_d">
       <span><?php _e($z_b_seating_plan_title); ?></span>
     </div>

  </div>
</div>
