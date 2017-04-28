jQuery(document).ready(function($) {

  $(document).on("click", ".admin_clear_sold_seats button", function(event) {

      event.preventDefault();

      var postID = $(this).data("postid");

      var data = {
        'action': 'clear_sold_seats',
        'postID' : postID,
        'userID' : hsf_tb_admin_plugin_data.userID

      };

      jQuery.post(hsf_tb_admin_plugin_data.ajax_url, data, function(response) {

        response = $.parseJSON(response);

        if (response) {
          $(".admin_clear_sold_seats").parent().append("<div>Cleared Sold Seats Data</div>")
        }


      });

  })

})
