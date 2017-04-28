<?php
$z_b_seating_plan = $seating_plans['general_seating_plan'];
$_hsf_seats_sold = get_post_meta( get_the_ID(), '_hsf_seats_sold_general_admission', true);
$_hsf_seats_sold = (int) $_hsf_seats_sold;
$z_b_seating_plan_qty = (int) $z_b_seating_plan['qty'];
$_hsf_seats_remain = $z_b_seating_plan_qty - $_hsf_seats_sold;
 ?>

<div class="container" data-price="<?php _e($z_b_seating_plan['price']);?>" data-qty="<?php _e($_hsf_seats_remain);?>">
    <div class="row">
        <div class="col-sm-12">
          <div class="general_admission_div">
            General <span></span> Admission
          </div>
        </div>
    </div>
</div>
