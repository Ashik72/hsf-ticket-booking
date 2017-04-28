<div class="zone_c_seating">


  <?php
  $z_b_seating_plan = $seating_plans['zone_c'];

   ?>

   <div class="title_z_a" style="display: none">
     <span><?php _e($z_b_seating_plan['title']) ?></span>
   </div>


  <div class="seating_z_c">

    <?php
    $title = $z_b_seating_plan["title"];

    $z_b_seating_plan_data = explode(PHP_EOL, $z_b_seating_plan['plan_data']);

    $z_b_seating_plan = explode(PHP_EOL, $z_b_seating_plan['plan']);
    //$z_b_seating_plan = array_map('intval', $z_b_seating_plan);
    $z_b_seating_plan = ( (is_array($z_b_seating_plan)) ? $z_b_seating_plan : [] );

    $needed_row = ( 12 / (count($z_b_seating_plan)));
    $needed_loop = count($z_b_seating_plan);
    $needed_row_temp = $needed_row;
        ?>

    <div class="container">
        <div class="row zone_a_container_row">
          <?php
          $temp_int_needed_row = 0;
          $default_seat_color = trim($default_seat_color);
          $html = "";

          $z_b_seating_max_count = 0;
          foreach ($z_b_seating_plan as $key => $z_b_seating_plan_value) {
            $z_b_seating_plan_value = (int) $z_b_seating_plan_value;
            if ($z_b_seating_max_count < $z_b_seating_plan_value)
              $z_b_seating_max_count = $z_b_seating_plan_value;

          }

          $needed_loop_temp = $needed_loop;

          $width_div = 100/$needed_loop_temp;
          $width_div = "25";

          while ($needed_loop > 0) {
            $z_b_seat_single = explode("|", $z_b_seating_plan[$temp_int_needed_row]);
            $z_b_seat_single_count = (int) $z_b_seat_single[0];
            $z_b_seat_single_count_total = $z_b_seat_single_count;


            //$html .= '<div class="zone_c_vertical zone_c_vertical_'.$needed_loop.'" style="width: '.$width_div.'px">';
            $html .= '<div class="zone_c_vertical zone_c_vertical_'.$needed_loop.'" style="width: auto">';
            $extra_seats = ($z_b_seating_max_count-$z_b_seat_single_count);
            $extra_seats_temp = $extra_seats;
            $height_of_blank_seats = 0;
            $height_p_s = ($height/($z_b_seat_single_count_total+$extra_seats));
            $height_p_s = "20";

            $extra_seats = (int) ($extra_seats/2);
            for ( ; $extra_seats > 0; $extra_seats--) {
              //$html .= "<div class='z_c_seat' style='height: ". ($height_p_s) ."px '><img src='".$seat_blank."'></div>";
              $html .= "<div class='z_c_seat z_c_seat_empty' style='height: ". ($height_p_s) ."px '><img src='".$seat_blank."'></div>";
            }

            $z_b_seating_plan_data_single = $z_b_seating_plan_data[$temp_int_needed_row];
            $z_b_seating_plan_data_single = explode("|", $z_b_seating_plan_data_single);
            $z_b_seating_plan_data_single[1] = trim($z_b_seating_plan_data_single[1]);
            $seat_number = 0;
            for ( ; $z_b_seat_single_count > 0; $z_b_seat_single_count--) {
              if ($z_b_seat_single_count_total == $z_b_seat_single_count)
                $class_f_a = "firstChild";
              else
                $class_f_a = "";

                $uid_string = str_replace(' ', '-', $z_b_seating_plan_data_single[0]); // Replaces all spaces with hyphens.
                $uid_string = preg_replace('/[^A-Za-z0-9\-]/', '', $uid_string); // Removes special chars.
                $uid = $uid_string.((int) $z_b_seating_plan_data_single[1])."_".$z_b_seat_single_count;

                $seat_number++;

              if (count($z_b_seat_single) == 1) {

                $html .= "<div data-seat_number='{$seat_number}' data-uid='{$uid}' data-price='{$z_b_seating_plan_data_single[1]}' data-name='{$z_b_seating_plan_data_single[0]}' class='z_a_seat z_c_seat_img {$class_f_a} {$uid}' style='height: ". ($height_p_s) ."px'><img src='".$default_seat_color."'></div>";

                continue;

              }

              $z_b_seat_color = $z_b_seat_single[1];

              $html .= "<div data-seat_number='{$seat_number}' data-uid='{$uid}' data-price='{$z_b_seating_plan_data_single[1]}' data-name='{$z_b_seating_plan_data_single[0]}' class='z_a_seat z_c_seat_img {$class_f_a} {$uid}' style='height: ". ($height_p_s) ."px'><img src='".$z_b_seat_color."'></div>";



            }

            $html .= '</div>';


            $temp_int_needed_row++;
            $needed_loop--;
          }

          _e($html);
           ?>

        </div>
    </div>





  </div>
</div>
