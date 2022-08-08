
$("#checkDB").click(function() {
    var dbloca	= $('#dblocal').val();
    var dbuser 	= $('#dbuser').val();
    var dbpass 	= $('#dbpass').val();
    var dbname 	= $('#dbname').val();

//	$(this).parents('.AccessoryItem').fadeOut('fast');

    $.ajax({
        cache: false,
        type: 'POST',
        url: '/install/index/checkdb',
        data: {
            "dbloca" : dbloca,
            "dbuser" : dbuser,
            "dbpass" : dbpass,
            "dbname" : dbname,

        },
        success: function(data)
        {

            if(data == 1) {
                $("#dbstatus").slideDown("fast", function () {
                    $('#dbstatus').html('Database Connection Successful.');
                    $('#dbstatus').removeClass('alert-danger').addClass('alert-success');
                });

            } else {
                $("#dbstatus").slideDown("fast", function () {
                    $('#dbstatus').html('Failed to Connect to Database. Check your settings and try again.');
                    $('#dbstatus').removeClass('alert-success').addClass('alert-danger');
                });

            }
        }
    });
    return false;
});



