<div class="content_bottom_bar">

<?php

//d($seating_plans);
$_hsf_seats_sold = get_post_meta( get_the_ID(), '_hsf_seats_sold', true);
//d($_hsf_seats_sold);

 ?>

<!-- <form method="post" id="content_bottom_bar_form"> -->
<div id="content_bottom_bar_form">

  <div class="title_mobile_seletc">
    SELECT YOUR SEAT
  </div>

<div class="select-zone_div ">

  <div class="title_mobile_seletc title_left_seat">
    SELECT YOUR ZONE
  </div>

  <select name="zone_select">

    <?php
    _e('<option></option>');

    foreach ($seating_plans as $key => $seating_plan_single) {
      _e('<option value="'.$key.'">'.$seating_plan_single['title'].'</option>');
    }

     ?>

  </select>


</div>
</div>

<!-- </form> -->

</div>
