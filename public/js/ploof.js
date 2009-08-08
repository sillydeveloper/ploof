// Thanks to Remy Sharp and nachokb. 
// http://github.com/nachokb/jquery-hint-with-password/tree/master
$(function(){ 
       // find all the input elements with title attributes
       $('input[title!=""]').hint();
});

// Thanks to Curvy Corners:
// http://www.curvycorners.net/
curvy= function(size, target)
{
    
    addEvent(window, 'load', function()
                            {
                                var settings = 
                                {
                                    tl: { radius: size },
                                    tr: { radius: size },
                                    bl: { radius: size },
                                    br: { radius: size },
                                    antiAlias: true
                                }
                                curvyCorners(settings, target);
                            });
}