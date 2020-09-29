$(function () {
    // init: side menu for current page
    $('li#menu-companies').addClass('menu-open active');
    $('li#menu-companies').find('.treeview-menu').css('display', 'block');
    $('li#menu-companies').find('.treeview-menu').find('.add-companies a').addClass('sub-menu-active');

    $('#companies-form').validationEngine('attach', {
        promptPosition : 'topLeft',
        scroll: false
    });

    $("#search").click(function(e){
        var id  = $("input[name=postcode]");
        if (id.val() == '') {
            alert('kosong bro');
            id.focus();
            return;
        }
        $.ajax({
            type: 'GET',
            url: '/postcode/search/' + id.val(),
            success: function(data) {
                console.log(data);
                $("input[name=city]").val(data.city);
                $("input[name=local]").val(data.local);
                var valofText = $("select[name=prefecture_id]" + " option").filter(function() {
                    return this.text == data.prefecture;
                }).val();
                $("select[name=prefecture_id]").val(valofText);
            },
            error: function(data) {
                console.log(data);
            }
        });
    });
    $(document).on("change","#takephoto",function (){
        readURL(this);
    });
    function readURL(input) {
        if (input.files && input.files[0]) {
          var reader = new FileReader();
          
          reader.onload = function(e) {
            $('#image').attr('src', e.target.result);
          }
          
          reader.readAsDataURL(input.files[0]);
        }
    }

    // init: show tooltip on hover
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body'
    });

    // show password field only after 'change password' is clicked
    $('#reset-button').click(function (e) {
        $('#reset-field').removeClass('hide');
    });
});
