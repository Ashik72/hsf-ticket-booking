jQuery(document).ready(function($) {

var dataQue = [];
window.dataQue = dataQue;
var generalAdmissionQue = [];
var global_seat_array_filtered = [];
var global_general_seat = [];
var global_unblock_seats = [];
var warning_stat = 0;

$('input[name="quantity_seats"]').change(function(evt) {

  var temp_val = $('input[name="seating_product_price_temp"]').val();
  var count = $(this).val();

  $('input[name="seating_price_total"]').val(temp_val*count);

});

$("#seating_plan_form").submit(function(event) {

  event.preventDefault();

  var formData = $( this ).serializeArray();

  $(".hsf_loader").css("display", "inline");

  var data = {
    'action': 'processFormDataQue',
    'formData' : dataQue,
    'postID' : hsf_tb_plugin_data.post_id
  };

  jQuery.post(hsf_tb_plugin_data.ajax_url, data, function(response) {

    console.log(response);
    response = $.parseJSON(response);

    $(".hsf_loader").css("display", "none");

    window.location.href = response;

  });

});


var stylingOpts = {

  z_a_height: function() {

    var seating_z_c_a_height = $(".seating_z_c .container").height();

    var zone_container_height = $(".col-sm-9.zone_container").height();
    var table_height = $(".ex1").height();
    var raised_seating_row_height = $(".raised_seating_row").height();

    //$(".col-sm-3.general_admission_container").height(zone_container_height);
    $(".col-sm-3.general_admission_container").height(table_height+raised_seating_row_height);
    var hex_cage_td_h = $(".hex_cage_td").height();
    $(".general_admission_div").css("margin-top", zone_container_height/2);
    console.log(hex_cage_td_h);
    //$(".the_fx_cage").css("margin-top", (hex_cage_td_h/3));
    console.log($(".seating_z_c .container").height());
  },

  select_seat: function() {

    $(document).on("hover", ".z_b_seat, .z_d_seat, .z_c_seat, .z_a_seat", function() {

      $(this).toggleClass("addOpacityHover");

    })

    $(document).on("click", ".z_b_seat, .z_d_seat, .z_c_seat, .z_a_seat", function() {
      var thisEl = $(this);

      if ($(this).hasClass("alreadySold"))
        return;

      if ($(this).hasClass("addOpacity")) {

        //alert("Test alert");

        $(this).removeClass("addOpacity");

        if (warning_stat == 0) {

          alert($(".warning_text_user_deselect").text());

          $(".warning_text_user_deselect").css("display", "block");

          warning_stat = 1;
        }

        var uid = $(this).data("uid");

        $.each(dataQue, function(i, val) {

           if (val.uid == uid) {
            console.log(val.uid);
            console.log(uid);
            console.log(i);
            var index = dataQue.indexOf(val);
            dataQue.splice(index, 1);
            return false;
           }

        });

        console.log(dataQue);


        return;
      }



      if ($(this).prev().hasClass("alreadySold")) {
        $(this).toggleClass("addOpacity");
        stylingOpts.add_price(thisEl);
        return;
      }

      if ($(this).hasClass("firstChild")) {
        $(this).toggleClass("addOpacity");
        stylingOpts.add_price(thisEl);
        return;
      }

      if ($(this).is(':first-child')) {
        $(this).toggleClass("addOpacity");
        stylingOpts.add_price(thisEl);
        return;
      }

      if (! $(this).prev().hasClass("addOpacity")) {

        alert("Please add previous seat");
        return;
      }

      $(this).toggleClass("addOpacity");
      stylingOpts.add_price(thisEl);

    })


  },

  add_price: function(el) {

    var price = el.data('price');
    var name = el.data('name');
    var uid = el.data('uid');
    var seat_number = el.data('seat_number');

    $(".select_seating_options .seating_select .col-sm-12 .selected_single_item").each(function(i, g_el) {


      console.log(uid);
      console.log($(this).data('uid'));
    })

    html_data = "";
    html_data += "<div class='selected_single_item uid_cart_"+uid+"' data-uid="+uid+">"+name + " [Seat number: "+seat_number+"] " +" @ $"+price+" <span class='removeItem'>x</span></div>";



    $(".select_seating_options .seating_select .col-sm-12").html($(".select_seating_options .seating_select .col-sm-12").html() + html_data);

    dataQue.push({
      'price' : price,
      'uid' : uid,
      'name' : name,
      'seat_number' : seat_number
    });

    $(".qty_seats_no").html(dataQue.length);
    var price_total = 0.0;
    $.each(dataQue, function(i, val) {

      val.price = parseFloat(val.price);
      val.price = Math.round( val.price * 1e2 ) / 1e2;

      price_total += val.price;

    })

    $(".qty_seats_price_no").html(price_total);

    console.log(dataQue);


  },

  roundFloat: function(p) {

  return (p.toFixed(2))/1;
  },

  removeItem: function() {


    $(document).on("click", ".selected_single_item .removeItem", function() {

      if (!$(this).parent().is(':last-child')) {
        alert("Please remove last ticket");
        return;
      };

      var uid = $(this).parent().data('uid');

      $("."+uid).removeClass("addOpacity");

      $(this).parent().remove();
      dataQue.pop();
      console.log(dataQue);
      $(".qty_seats_no").html(dataQue.length);
      var price_total = 0.0;
      $.each(dataQue, function(i, val) {
        price_total += parseFloat(val.price);

      })

      $(".qty_seats_price_no").html(price_total);


    })

  },

  general_admission: function() {

    $(document).on("click", ".general_admission_container", function() {

      generalAdmissionQue.push(" ");

      var price = $(this).children(".container").data('price');
      var qty = $(this).children(".container").data('qty');
      var name = "General Admission";
      var uid = "general_admission_"+generalAdmissionQue.length;


      if (qty <= 0) {

        alert("General admission seats not available");

        return;

      }


      dataQue.push({
        'price' : price,
        'uid' : uid,
        'name' : name,
        'seat_number' : "",
        'general_admission' : 1
      })

      $(".qty_seats_no").html(dataQue.length);
      var price_total = 0.0;
      $.each(dataQue, function(i, val) {
        price_total += parseFloat(val.price);

      })

      $(".qty_seats_price_no").html(price_total);

      html_data = "";
      html_data += "<div class='selected_single_item uid_cart_"+uid+"' data-uid="+uid+">"+name + " " +" @ $"+price+" <span class='removeItem'>x</span></div>";

      $(".select_seating_options .seating_select .col-sm-12").html($(".select_seating_options .seating_select .col-sm-12").html() + html_data);

      console.log(dataQue);

      qty = $(this).children(".container").data('qty', (qty-1));

    })

  },

  load_sold_seats: function() {

    $.each(hsf_tb_plugin_data.seats_sold, function(i, val) {

      $("."+val).addClass("alreadySold");

    })
  },

  tooltip: function() {
    $( ".z_b_seat, .z_a_seat, .z_c_seat, .z_d_seat" ).tooltip({title: (function() {
        var name = $(this).data('name');
        var seat_number = $(this).data('seat_number');
        return name+" "+seat_number;
    }), placement: "top"});

  },

  setBG: function() {

    //$('.seating_plans').css('background-image', 'url(' + hsf_tb_plugin_data.background_img + ')');

    //console.log($('.hsf_ticket_page .woocommerce-Tabs-panel'));

    $('.content_hsf_80').css('background-image', 'url(' + hsf_tb_plugin_data.background_img + ')');

  },

  adjustBottomBar: function() {

    console.log($("#et_mobile_nav_menu").css("display") == "none");

    if ($("#et_mobile_nav_menu").css("display") == "none") {
      $(".bottom_ordering_bar").css("display", "none");
      return;
    }

    //$(".bottom_ordering_bar").css("max-height", ((5/100)*$("body").height()));

    // $(".bottom_ordering_bar").height();

  },

  mobileWidth: function() {

    if ($('body').width() > 768)
      return;

      console.log($(".row.zone_b_seating_row .zone_b").height());

    $(".col-sm-3.general_admission_container").height($(".row.center_seating_row").height()+50);
    $(".col-sm-3.general_admission_container").css("margin-top", $(".row.zone_b_seating_row").height() / 1.49);
    //$(".general_admission_div").css("margin-top", ($(".col-sm-3.general_admission_container").height()/5));
    $(".general_admission_div").css("margin-top", "0");

  },

  bottomSelect: function() {

    $(".select-zone_div select[name='zone_select']").change(function() {

      console.log($(this).val());

      $(".addRemoveDiv").remove();


      var data = {
        'action': 'bottomSelectZoneGetRow',
        'zone_val' : $(this).val(),
        'postID' : hsf_tb_plugin_data.post_id
      };

      jQuery.post(hsf_tb_plugin_data.ajax_url, data, function(response) {

        response = $.parseJSON(response);

        console.log(response);

        var html = "";

        if (typeof response.general_seating != "undefined" && response.general_seating > 0) {

          $(".select-row_div select").remove();



  if ($(".addRemoveDiv").length > 0)
    $(".addRemoveDiv").remove();

  var html = "";
  html += '<div class="addRemoveDiv">';

  html += '<button class="addRemoveIcon">+</button><span><input data-general_seating="1" type="text" name="addRemoveSeat" value="'+dataQue.length+'"></span><button class="addRemoveIcon">-</button>';

  html += '</select>';
  html += '</div>';

  $("#content_bottom_bar_form .select-zone_div").append(html);

  $(".bottom_ordering_bar").css("max-height", "none");

  temp_seat_obj = {
    'price' : response.price,
    'uid' : 'general_admission_1',
    'name' : 'General Admission',
    'seat_number' : '',
    'general_admission' : 1
  };

  global_general_seat.push(temp_seat_obj);

          return;
        }

        if ($(".select-row_div").length == 0) {



          html += '<div class="select-row_div">';
          html += '  <div class="title_mobile_seletc title_left_seat">SELECT YOUR ROW</div>';
          html += '<div><select name="zone_row_select">';
          html += '<option value="0"></option>';

          jQuery.each( response.plan_data, function( i, plan_data_single ) {

            html += '<option value="0" data-row_title="'+response.plan_data[i]+'" data-row_zone="'+response.plan_zone+'" data-row_qty="'+response.plan_qty[i]+'" data-row_price="'+response.price[i]+'">'+plan_data_single+'</option>';

          })

           html += '</select></div>';
           html += '</div>';

           $("#content_bottom_bar_form").append(html);

        } else {

          $(".select-row_div select").remove();

          html += '<div><select name="zone_row_select">';
          html += '<option value="0"></option>';

          jQuery.each( response.plan_data, function( i, plan_data_single ) {

            html += '<option value="0" data-row_title="'+response.plan_data[i]+'" data-row_zone="'+response.plan_zone+'" data-row_qty="'+response.plan_qty[i]+'" data-row_price="'+response.price[i]+'">'+plan_data_single+'</option>';

          })

           html += '</select></div>';

           $("#content_bottom_bar_form .select-row_div").append(html);

           $(".bottom_ordering_bar").css("max-height", "none");

        }




      })



    });

$(document).on("change", "select[name='zone_row_select']", function() {
  //$("select[name='zone_row_select']").change(function() {
  var element = $(this).find("option:selected");
  var row_title = element.data("row_title");
  var row_zone = element.data("row_zone");
  var row_qty = element.data("row_qty");
  var row_price = element.data("row_price");

  var data = {
    'action': 'zone_c_row_get_seats',
    'row_title' : row_title,
    'row_zone' : row_zone,
    'row_qty' : row_qty,
    'row_price' : row_price,
    'postID' : hsf_tb_plugin_data.post_id
  };

  var seat_array = [];

  jQuery.post(hsf_tb_plugin_data.ajax_url, data, function(response) {

    response = $.parseJSON(response);
    seat_array = response;


  }).done(function() {

    var seat_array_filtered = [];

    var temp_seat_obj = new Object();

    seat_array_filtered.length = 0;
    global_seat_array_filtered.length = 0;

    console.log(seat_array);

    jQuery.each( seat_array, function( i, seat_array_single ) {
      if ($("."+seat_array_single).hasClass("alreadySold"))
        return;

      temp_seat_obj = {
        'price' : row_price,
        'uid' : seat_array_single[0],
        'name' : seat_array_single[2],
        'seat_number' : seat_array_single[1]
      };

      seat_array_filtered.push(temp_seat_obj);
      global_seat_array_filtered.push(temp_seat_obj);

    });

    seat_array_filtered.reverse();
    global_seat_array_filtered.reverse();

    if ($(".addRemoveDiv").length > 0)
      $(".addRemoveDiv").remove();

    console.log(global_seat_array_filtered);

    var html = "";
    html += '<div class="addRemoveDiv">';

    html += '<button class="addRemoveIcon">+</button><span><input type="text" name="addRemoveSeat" value="'+dataQue.length+'"></span><button class="addRemoveIcon">-</button>';

    html += '</select>';
    html += '</div>';

    //$("#content_bottom_bar_form .select-row_div").append(html);
    $("#content_bottom_bar_form").append(html);

    $(".bottom_ordering_bar").css("max-height", "none");



    //$(".content_bottom_bar").height( $(".content_bottom_bar").height() + $("#ms-select-mobile-seat").height())

  })


})



    $(document).on("click", ".addRemoveIcon", function(event) {

      event.preventDefault();

      var text_val = $(this).text();
      var general_seating =  ( typeof $('input[name="addRemoveSeat"]').data('general_seating') != "undefined" ) ? 1 : 0;


      if (text_val == "+") {

        $('input[name="addRemoveSeat"]').val( parseInt($('input[name="addRemoveSeat"]').val()) + 1);
        console.log($('input[name="addRemoveSeat"]').val());

        if (general_seating >= 1) {

          // dataQue.push({
          //   'price' : price,
          //   'uid' : uid,
          //   'name' : name,
          //   'seat_number' : "",
          //   'general_admission' : 1
          // })
          dataQue.push(global_general_seat[0]);

        } else {

          if (global_seat_array_filtered.length <= 0)
            return;

          var element = global_seat_array_filtered.pop();
          dataQue.push(element);
          $("."+element.uid).addClass("addOpacity");

        }



        $(".qty_seats_no").html(dataQue.length);
        var price_total = 0.0;
        $.each(dataQue, function(i, val) {

          val.price = parseFloat(val.price);
          val.price = Math.round( val.price * 1e2 ) / 1e2;

          price_total += val.price;

        })

        $(".qty_seats_price_no").html(price_total);



      }

      if (text_val == "-") {

        if ($('input[name="addRemoveSeat"]').val() <= 0)
          return;

        if (dataQue.length <= 0)
          return;

        $('input[name="addRemoveSeat"]').val(parseInt($('input[name="addRemoveSeat"]').val()) - 1);
        console.log($('input[name="addRemoveSeat"]').val());


        if (general_seating >= 1) {

          // dataQue.push({
          //   'price' : price,
          //   'uid' : uid,
          //   'name' : name,
          //   'seat_number' : "",
          //   'general_admission' : 1
          // })
          //dataQue.push(global_general_seat[0]);

          var element = dataQue.pop();

        } else {

          var element = dataQue.pop();
          global_seat_array_filtered.push(element);
          $("."+element.uid).removeClass("addOpacity");


        }



        $(".qty_seats_no").html(dataQue.length);
        var price_total = 0.0;
        $.each(dataQue, function(i, val) {
          price_total += parseFloat(val.price);

        })

        $(".qty_seats_price_no").html(price_total);



      }

      console.log(dataQue);

    })




  },

  adminSeatManage: function() {

    if (hsf_tb_plugin_data.isAdmin != 1)
      return;

      var html = "";

      html += "<div class='adminOptions'>"
      html += "<button class='unblockThisSeats'>Unblock These Seats</button>"
      html += "<button class='blockThisSeats'>Block These Seats</button>"
      html += "</div>";
      $("#seating_plan_form").parent().append(html);


      $(document).on("click", ".z_b_seat, .z_d_seat, .z_c_seat, .z_a_seat", function() {
        var thisEl = $(this);


        if ($(this).hasClass("alreadySold")) {

          global_unblock_seats.push($(this).data("uid"));
          $(this).removeClass("alreadySold");
          return;

        }

      })

      $(document).on("click", ".unblockThisSeats", function() {

        if (global_unblock_seats.length < 1)
          return;

          var data = {
            'action': 'clear_sold_seats_individually',
            'seat_data' : global_unblock_seats,
            'postID' : hsf_tb_plugin_data.post_id
          };


          jQuery.post(hsf_tb_plugin_data.ajax_url, data, function(response) {

            response = $.parseJSON(response);

            if (response == 1) {

              alert("Successfully unblocked selected seats. Refreshing...")
              location.reload();

            } else {
              alert("Something went wrong.")

            }



          })


      });

      ///

      $(document).on("click", ".blockThisSeats", function() {

        var data = {
          'action': 'processFormDataQueAdmin',
          'formData' : dataQue,
          'postID' : hsf_tb_plugin_data.post_id
        };

        jQuery.post(hsf_tb_plugin_data.ajax_url, data, function(response) {

          response = $.parseJSON(response);

          if (response == 1) {

            alert("Successfully blocked selected seats. Refreshing...")
            location.reload();

          } else {
            alert("Something went wrong.")
          }

        });


      });

      //



    console.log(dataQue);

  }

}

stylingOpts.z_a_height();
stylingOpts.select_seat();
stylingOpts.removeItem();
stylingOpts.general_admission();
stylingOpts.load_sold_seats();
stylingOpts.tooltip();
stylingOpts.setBG();
stylingOpts.adjustBottomBar();
stylingOpts.mobileWidth();
stylingOpts.bottomSelect();
stylingOpts.adminSeatManage();

console.log($(".Zone-C-row-1-50_23").hasClass("alreadySold"));

})
