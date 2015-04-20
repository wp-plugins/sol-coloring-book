(function($) {
    
    window.addEventListener('load', eventWindowLoaded, false);
    var undo_element = $('g')[0];
    var undo_to_color = "white";    
    var tip = 'marker';                                     //default settings
    $("#marker").css("background-color","black");


    function eventWindowLoaded() {
       add_coloring_book_events();
    }

    function cleartip(oldtip) {
        $("#brush").css("background-color","white");
        $("#crayon").css("background-color","white");
        $("#marker").css("background-color","white");
        $("#pencil").css("background-color","white");
        
        $('#colors input').removeClass(tip+'-out');         //change the color choices too (remove oldtip class, both out and in)
        $('#colors input').removeClass(tip);
    }
    function seltip(tiptype) {
        $("#"+tiptype).css("background-color","black");
        $('#colors input').addClass(tiptype);               //change all of the colors to be this style tip
    }

    function add_coloring_book_events() {
        var g = $('#g-code-main');
        g.children().each(function() {
            $(this).css('fill','white');                    //force each to be clickable
        });
        
        // Add click events for colorable portions of drawing -- Oddly,  selector $('path.colorable') does not work in iBooks reader, tho does in Mobile Safari
        g.children().bind("click", function(event) {
            //alert('clicked');//******
	        event.preventDefault();                         // Suppress default; helpful on touchscreen devices
	        undo_element = this;                            // Get current element and color and save it in undo_element and undo_to_color variables
            undo_to_color = $(this).css("fill");
            //console.log(undo_to_color);//****
	        $('#undo_redo').attr("value", "Undo");          // Toggle "Undo" button to make sure it says "Undo" (it might say "Redo")
	        color_chosen = $("#color_chosen").html();       // Set fill of clicked portion of drawing to color chosen in palette below
            if (tip=="marker") {
                $(this).css("fill", color_chosen); 
            } else {
                $(this).css("fill","url(#img"+tip+"-"+color_chosen+")");
                //console.log("filling:#img"+tip+"-"+color_chosen);//******
            }
        });
        
        $('#colorpage_logo_link').bind("click", function(event) {  // Add click events for logo
            window.open("http://www.mysummersol.com","_blank");
        });
        $('.color_choice').bind("click", function(event) {  // Add click events for color palette - gets color from id
           color_chosen = $(this).attr("id");
           $("#color_chosen").html(color_chosen);           // ******** replace this by making clicked on one be longer
           $('#colors input').removeClass(tip+'-out');   //force all to become short
           //$('#colors input').addClass(tip);            
           $(this).toggleClass(tip+'-out',tip);      //make the clicked on one long
        });

        $('#brush').click(function() {  cleartip(tip); seltip("brush"); tip = "brush";    });
        $('#crayon').click(function() { cleartip(tip); seltip("crayon"); tip = "crayon";    });
        $('#marker').click(function() { cleartip(tip); seltip("marker"); tip = "marker";    });
        $('#pencil').click(function() { cleartip(tip); seltip("pencil"); tip = "pencil";    });
        
        $('#reset_image').bind("click", function(event) {
	        g.children().each(function() {
                $(this).css('fill','white');               //force each to be white again
            });
        });

        $('#undo_redo').bind("click", function(event) {
	        existing_color = $(undo_element).css("fill");  // First, save existing color of element we're going to undo
	        $(undo_element).css("fill", undo_to_color);    // Now revert color back to undo_to_color
	        undo_to_color = existing_color;                 // Finally, make existing_color new undo_to_color, to support "Redo" functionality
	        if ($(this).attr("value") == "Undo") {          // If button is named "Undo", rename it "Redo" and vice versa
	            $(this).attr("value", "Redo");
	        } else {
	            $(this).attr("value", "Undo");
	        }
        });
    }
})( jQuery );