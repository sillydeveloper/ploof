debug= function(val)
{
    if ($('body').children('#debug').length < 1)
    {
        $('body').prepend('<div id="debug"></div>');
    }
    $('#debug').append("<div>DEBUG:"+val+"</div>");
}

get_view_id_parts= function(view_id)
{
    var split= view_id.split(/_/);
    var results= new Array();
    results['object']= split[0];
    results['id']= split[1];
    results['field']= split[2];
    return results;
}

// used by the form_checkbox() to update the hidden field value:
toggle_checkbox= function(targ, val)
{
    if ($('#'+targ).attr('checked'))
        $('#hidden_'+targ).val($('#'+targ).val());
    else
        $('#hidden_'+targ).val('');
}


// make radio buttons matching `targ` 
//  with different names act like one radio:
one_radio= function(targ)
{
    $(document).ready(
        function()
        {
            $(targ).bind('mousedown keydown', 
                function(e) 
                {  
                    if (e.type == 'keypress' && e.charCode != 32) 
                           return false;
               
                    var obj = e.target;
            
                    // turn all other ones off:
                    $(targ).each(
                        function(i, val)
                        {
                            if ($(this).attr('checked'))
                                $(this).attr('checked', false);
                        }
                    );
                
                    // turn this one on:
                    $(obj).attr('checked', true);
                }
            );
        }
    );
}

function is_array(input)
{
    return typeof(input)=='object'&&(input instanceof Array);
}
