

//ContaCT js*************
$('#contactForm').on('submit', function(e) {
    // alert('hi');
    e.preventDefault();
    var data = new FormData(this);
    var formAction = $(this);
    var btnAction = $('#ContactBtn');
    var spinAction = $('#contactSpin');

    if ($(formAction).parsley().isValid() == true) {
        $.ajax({
            type: 'POST',
            url: $(formAction).attr('action'),
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(btnAction).attr("disabled", true);
                $(spinAction).show();
            },
            success: function(res) {
                $(btnAction).removeAttr("disabled");
                $(spinAction).hide();
                console.log(res);
                // alert($.trim(res));
                if ($.trim(res) == 'success') {
                    // alert(1);
                    // $.notify("Data Submitted", "success");
                    $('#contactForm')[0].reset();

                    $('#enqmsg').css('display', 'block');

                    window.setTimeout(function() {
                        $('#enqmsg').css('display', 'none');
                    }, 3000);
                } else if ($.trim(res) == 'error') {
                    $.notify("Data Not Submitted", "error");
                } else {
                    // Handle unexpected response
                    console.log("Unexpected response:", res);
                }

            },
            error: function() {
                // window.location.reload();
            }
        });
    }
});
