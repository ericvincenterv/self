 $(function(){
    var jcrop_api;

    $( "body" ).on( "click", '#jcrop-btn', function() {
        checkCoords();
        crop(jcrop_api);
    });

    $( "body" ).on( "click", '.crop-open-modale', function() {
        $("#cropbox").attr("src",$(this).data("url"));
        $("#crop-url").val($(this).data("url"));
        
        $('#cropbox').Jcrop({
            aspectRatio: 1.435,
            onSelect: updateCoords
        },function(){
            jcrop_api = this;
        });

        $('#modal-image-crop').modal('show');
    });
});
 
function updateCoords(c)
{
    $('#x').val(c.x);
    $('#y').val(c.y);
    $('#w').val(c.w);
    $('#h').val(c.h);
};

function checkCoords()
{
    if (parseInt($('#w').val())) return true;
    alert('Select where you want to Crop.');
    return false;
};

function crop(jcrop_api)
{
    var url = $("#crop-url").val();
    var x = $("#x").val();
    var y = $("#y").val();
    var w = $("#w").val();
    var h = $("#h").val();

    $.ajax({
        url: Routing.generate('editor_crop_image'),
        type: 'PUT',
        data: 
        { 
            x:x,
            y:y,
            w:w,
            h:h
        }
    })
    .done(function(data) {
        console.log(data);
        jcrop_api.destroy();
        $("#modal-image-crop").modal('hide');
    });
};

