<div class="warning_text_user_deselect">
  <?php _e($seating_plans['warning_text_user_deselect']['data']); ?>
</div>
<div class="zone_b_seating">


  <?php
  $z_b_seating_plan = $seating_plans['zone_b'];

   ?>


  <div class="title_z_b">
    <span><?php _e($z_b_seating_plan['title']) ?></span>
  </div>

  <div class="seating_z_b">

    <?php

    $z_b_seating_plan_data = explode(PHP_EOL, $z_b_seating_plan['plan_data']);

    $z_b_seating_plan = explode(PHP_EOL, $z_b_seating_plan['plan']);
    //$z_b_seating_plan = array_map('intval', $z_b_seating_plan);
    $z_b_seating_plan = ( (is_array($z_b_seating_plan)) ? $z_b_seating_plan : [] );

    $default_seat_color = trim($default_seat_color);
    $html = "";

    $z_b_seating_max_count = 0;
    foreach ($z_b_seating_plan as $key => $z_b_seating_plan_value) {
      $z_b_seating_plan_value = (int) $z_b_seating_plan_value;
      if ($z_b_seating_max_count < $z_b_seating_plan_value)
        $z_b_seating_max_count = $z_b_seating_plan_value;

    }

    //$width = (100/$z_b_seating_max_count) - 1;
    $width = (80/$z_b_seating_max_count) - 1;
    $int_data_count = 0;
    foreach ($z_b_seating_plan as $key => $z_b_seat) {
      $z_b_seat = explode("|", $z_b_seat);
      $z_b_seating_plan_data_single = $z_b_seating_plan_data[$int_data_count];
      $int_data_count++;

      $difference_from_max = ( $z_b_seating_max_count - (int) $z_b_seat[0] );

      $z_b_seat_temp = (int) $z_b_seat[0];

      $total_seats = $z_b_seat_temp + $difference_from_max;
      $z_b_seating_plan_data_single = (empty($z_b_seating_plan_data_single) ? "" : explode("|", $z_b_seating_plan_data_single)) ;




      $seat_number = 0;

      if (count($z_b_seat) == 1) {


        $z_b_seat = (int) $z_b_seat[0];

        $html .= "<div class='z_b_seat_holder a'>";

        for ($i = ($difference_from_max/2); $i > 0 ; $i--) {
          //$html .= "<div class='z_b_seat' style='width: {$width}%'><img src='".$seat_blank."'></div>";
        }
        while ($z_b_seat > 0) {

          $uid_string = str_replace(' ', '-', $z_b_seating_plan_data_single[0]); // Replaces all spaces with hyphens.
          $uid_string = preg_replace('/[^A-Za-z0-9\-]/', '', $uid_string); // Removes special chars.
          $uid = $uid_string.((int) $z_b_seating_plan_data_single[1])."_".$z_b_seat;

          $seat_number++;
          $html .= "<span class='z_b_seat z_b_seat_unique {$uid}' data-uid='{$uid}' data-seat_number='{$seat_number}' data-price='{$z_b_seating_plan_data_single[1]}' data-name='{$z_b_seating_plan_data_single[0]}' style='width: {$width}%'><img style='width: {$width}%; margin: 1px; padding: 0' src='".$default_seat_color."'></span>";

          $z_b_seat--;
        }
        $html .= "</div>";

        continue;
      }


      $z_b_seat_no = (int) $z_b_seat[0];
      $z_b_seat_color = $z_b_seat[1];

      $html .= "<div class='z_b_seat_holder b'>";
      for ($i = ($difference_from_max/2); $i > 0 ; $i--) {
        //$html .= "<div class='z_b_seat' style='width: {$width}%'><img src='".$seat_blank."'></div>";
      }
      $seat_number = 0;

      while ($z_b_seat_no > 0) {
        $seat_number++;
        $uid_string = str_replace(' ', '-', $z_b_seating_plan_data_single[0]); // Replaces all spaces with hyphens.
        $uid_string = preg_replace('/[^A-Za-z0-9\-]/', '', $uid_string); // Removes special chars.
        $uid = $uid_string.((int) $z_b_seating_plan_data_single[1])."_".$z_b_seat_no;

        $html .= "<span class='z_b_seat z_b_seat_unique {$uid}' data-uid='{$uid}' data-seat_number='{$seat_number}' data-price='{$z_b_seating_plan_data_single[1]}' data-name='{$z_b_seating_plan_data_single[0]}' style='width: {$width}%'><img style='width: {$width}%; margin: 1px; padding: 0' src='".$z_b_seat_color."'></span>";


        $z_b_seat_no--;
      }
      $html .= "</div>";


    }

    _e($html);

     ?>


  </div>
</div>
