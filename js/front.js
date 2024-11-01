/* 
 Created on : 04-Apr-2016, 11:37:37
 Author     : Matt
 */
var globaltimer;

jQuery(document).ready(function ($) {
    /* var yjpostscount=1;
     $(".yj-posts").each(function() {
     $(this).attr("id",'yj-posts-'+yjpostscount);
     yjpostscount++;
     });
     var YJMorepostscount=1;
     $(".yj-more-posts").each(function() {
     $(this).attr("id",'yj-more-posts-'+YJMorepostscount);
     YJMorepostscount++;
     });*/

    var timeout = null;

    $(".yj-more-posts").on("click", function () { // When btn is pressed.
        var yjposts = $(this).closest('.yj-more-posts').attr('id');
        yjposts = yjposts.replace("-more", "");
        //console.log(yjposts)
        if (!$("body #" + yjposts).attr("disabled")) {//Stop loading when there is nothing more to load / loading
            $(this).attr("disabled", "true"); // Disable the button, temp.       
            load_posts(yjposts);
        }

    });

    //Load on scroll
    $(window).bind("scroll touchstart", function () {
        jQuery(".yj-posts").each(function () {
            yjposts = jQuery(this).attr("id");
            if (!timeout) {
                timeout = setTimeout(function () {

                    clearTimeout(timeout);
                    timeout = null;
                    offset = $("body #" + yjposts + ".yj-posts").offset();
                    position = $("body #" + yjposts + ".yj-posts").position();
                    scrollTop = $("body").scrollTop();
                    scrollTop = scrollTop + $(window).height();
                    docheight = $("body #" + yjposts + ".yj-posts").height();
                    combinedtrigger = offset.top + docheight;

                    var disattr = $("body #" + yjposts + ".yj-posts").data("disabled");

                    if (disattr == false) {
                        var loadtype = $("body #" + yjposts + ".yj-posts").data("loadtype");
                        if (loadtype == "lazy") {

                            if (scrollTop >= combinedtrigger) {
                                $("body #" + yjposts + ".yj-posts").data("disabled") == "true"; // Disable the button, temp.                             
                                load_posts(yjposts);
                            }
                        }
                    }
                }, 500);
            }
        })


    });

    // Debug column width
    $('.dev-cols').on('click', function (e) {
        changeColClass(this, true);
    }).on('contextmenu', function (e) {
        e.preventDefault();
        changeColClass(this, false);
    });

    $('div.content-area').on('click', '.link-image', function(){
        location.href = $(this).closest('a').attr('href');
    });

}).ajaxComplete(function () {
    //gridResize();
    clearTimeout(globaltimer);
    globaltimer = setTimeout(gridResize, 1000);
});

jQuery(window).on("load resize", function (e) {

    clearTimeout(globaltimer);
    //globaltimer = setTimeout(gridResize, 500);
    gridResize();
});

function load_posts(yjposts) {
    pageNumber = jQuery("body #" + yjposts).data("pagenumber");
    pageNumber++;
    //console.log('body #' + yjposts + ' div.atts');
    jQuery("body #" + yjposts).data("pagenumber", pageNumber);

    jQuery.ajax({
        type: "POST",
        dataType: "html",
        url: ajax_posts.ajaxurl,
        data: {action: 'more_post_ajax', atts: jQuery('body #' + yjposts + ' div.atts').text(), paged: pageNumber},
        success: function (data) {
            //console.log("success");
            var $data = jQuery(data);
            //console.log($data)
            //console.log($data.length)
            if ($data.length > 0) {
                //console.log("appending to body #" + yjposts)
                jQuery("body #" + yjposts).append($data);
               jQuery("body #" + yjposts).data("disabled") == "false";
            } else {
                YJMorebutton = yjposts.replace("yj-", "yj-more-");
                jQuery("body #" + yjposts).data("disabled") == "true";
                jQuery("body #" + YJMorebutton + " a").text("No more");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            //console.log("error");
            $loader.html(jqXHR + " :: " + textStatus + " :: " + errorThrown);
        }

    });
    return false;
}

function gridResize() {
    jQuery(".yj-posts").each(function () {
        var yjCurrentMasonry = jQuery(this).attr("id");
        //console.log(yjCurrentMasonry)

        //console.log("running");
        var template = jQuery("#" + yjCurrentMasonry + ".yj-posts").data("template"); // Template slug
        if (jQuery("#" + yjCurrentMasonry + ".yj-posts").length) {
            //Settings
            if (jQuery("#" + yjCurrentMasonry + ".yj-posts").hasClass("gapfill")) {
                var gapfill = true;
                //console.log("## GAPFILL ON");
            } else {
                var gapfill = false;
            }

            var biggest = 0;
            var screenwidth = (window.innerWidth > 0) ? window.innerWidth : screen.width;
            var containwidth = jQuery("#" + yjCurrentMasonry + ".yj-posts").width();
            var singlecolwidth = containwidth / 12;

            var colamount = 0;
            var currentcol = 0;

            var colwidth = containwidth / 12;

            var heightArray = [];
            var heightArray1 = [];

            var heightArrayfinesse = [];

            var heightToGrow = 999999999999999;

            var colArray = {col0: 0, col1: 0, col2: 0, col3: 0, col4: 0, col5: 0, col6: 0, col7: 0, col8: 0, col9: 0, col10: 0, col11: 0};
            var colIDArray = {col0: "", col1: "", col2: "", col3: "", col4: "", col5: "", col6: "", col7: "", col8: "", col9: "", col10: "", col11: ""}

            var xpos = 0;
            var ypos = 0;
            var xwidth = 0;
            var ylength = 0;
            var griditemCount = 0;
            var templateStep = 0;

            if (template !== "custom") {
                var templateArray = template.split(":");
                var templateArrayLength = templateArray.length;
                //console.log(templateArray)
                //console.log(templateArrayLength)
            }
            jQuery("#" + yjCurrentMasonry + " .griditem").each(function () {

                if (template == "custom" || jQuery(this).data('override') > 0) {
                    ;
                } else {
                    if (templateStep == templateArrayLength) {
                        templateStep = 0
                    }

                    jQuery(this).removeClass("col-sm-1 col-sm-2 col-sm-3 col-sm-4 col-sm-5 col-sm-6 col-sm-7 col-sm-8 col-sm-9 col-sm-10 col-sm-11 col-sm-12");
                    jQuery(this).addClass("col-sm-" + templateArray[templateStep]);

                    templateStep = templateStep + 1;
                }

                griditemCount = griditemCount + 1;
                //console.log("###### NEW GRID ITEM ############");
                xwidth = jQuery(this).outerWidth();
                xwidth = Math.round(xwidth);

                ylength = jQuery(this).outerHeight();
                ylength = Math.round(ylength);

                colamount = xwidth / colwidth;

                currentcol = Math.round(currentcol);
                colamount = Math.round(colamount);

                if (currentcol == 12) {
                    //console.log(currentcol);
                    currentcol = 0;
                }

                //handle image blocks
                if (jQuery(this).hasClass("format-image")) {
                    var heightcalc = jQuery(this).find(".gridcontainer").data("imagewidth") / jQuery(this).find(".gridcontainer").data("imageheight");
                    ylength = xwidth / heightcalc;
                    ylength = Math.round(ylength);

                    jQuery(this).css("height", ylength + "px");
                }

                varname = "col" + currentcol;

                //find make array of all col heights
                var heightArrayall = [];
                for (i = 0; i <= 11; i++) {
                    varnameall = "col" + i;
                    heightArrayall.push(colArray[varnameall]);
                }

                var heightArrayallstart = [];
                var startpoint = 11 - (colamount - 1);

                heightArrayallstart = heightArrayall;
                heightArrayallstart.slice(startpoint, colamount - 1);

                var postobeexcluded = [];

                if (template == "custom") {
                    currentcol = refinedFindlowestpos(heightArrayall, heightArrayallstart, colamount, postobeexcluded, colArray, startpoint);
                } else {
                    //findlowestpos(heightArrayall, heightArrayallstart, colamount, postobeexcluded, colArray, startpoint);
                }
                //tweak to fit left col
                if (currentcol <= 3 && currentcol !== 0) {
                    if (colArray["col" + currentcol] == colArray["col0"] && colArray["col" + currentcol] >= colArray["col1"] && colArray["col" + currentcol] >= colArray["col2"]) {
                        currentcol = 0;
                    }
                }
                //fill column gaps
                if (gapfill == true) {
                    if ((currentcol + colamount) >= 11) {
                        jQuery(this).removeClass("col-sm-1 col-sm-2 col-sm-3 col-sm-4 col-sm-5 col-sm-6 col-sm-7 col-sm-8 col-sm-9 col-sm-10 col-sm-11 col-sm-12");

                        jQuery(this).removeClass("col-sm-" + colamount);
                        var adjustedcols = 12 - currentcol;
                        colamount = adjustedcols;
                        adjustedcols = adjustedcols
                        jQuery(this).addClass("col-sm-" + adjustedcols);

                        //recalculate for resized div
                        xwidth = jQuery(this).outerWidth();
                        ylength = jQuery(this).outerHeight();
                        colamount = xwidth / colwidth;
                        colamount = Math.round(colamount);
                    }

                    if ((currentcol + colamount) == 13) {
                        currentcol = 0;
                    }
                    ;
                } else {
                    if ((currentcol + colamount) >= 13) {
                        currentcol = 0;
                    }
                }

                xpos = colwidth * (currentcol);

                varname = "col" + currentcol;
                //make array of col heights between current col and ending col
                for (i = currentcol; i <= currentcol + colamount - 1; i++) {
                    varname = "col" + i;
                    heightArray.push(colArray[varname]);
                }

                var maxheight = Math.max.apply(Math, heightArray);

                jQuery(this).attr("data-colwidth", colamount);
                jQuery(this).attr("data-startcol", currentcol);
                jQuery(this).attr("data-ylength", ylength);


                var newObject1 = JSON.stringify(colArray);
                var tmpcolArray = JSON.parse(newObject1);

                var newObject2 = JSON.stringify(colIDArray);
                var tmpcolIDArray = JSON.parse(newObject2);

                for (i = currentcol; i <= currentcol + colamount - 1; i++) {
                    varname = "col" + i;

                    colArray[varname] = maxheight + ylength;
                    colIDArray[varname] = jQuery(this).attr("id");

                    if (colArray[varname] > biggest) {
                        biggest = parseInt(colArray[varname]);
                    }
                }

                ypos = colArray[varname] - ylength;

                jQuery(this).attr("data-ypos", ypos);

                if (gapfill == true) {

                    for (h = currentcol; h <= currentcol + colamount - 1; h++) {

                        tmpvarname = "col" + h;

                        colArray[tmpvarname] = maxheight + ylength;
                        colIDArray[tmpvarname] = jQuery(this).attr("id");

                        if (colArray[tmpvarname] > biggest) {
                            tmpbiggest = parseInt(colArray[tmpvarname]);
                        }
                        tmpcurrentcol = h;
                    }

                    currentstart = jQuery(this).data("startcol");
                    currentend = currentstart + jQuery(this).data("colwidth") - 1;

                    currentblocknames = [];
                    currentblocknamesObj = {};

                    for (i = currentstart; i <= currentend; i++) {
                        varname = "col" + i;

                        if (currentblocknames.indexOf(tmpcolIDArray[varname]) == -1 && tmpcolIDArray[varname] !== jQuery(this).attr("id")) {
                            currentblocknames.push(tmpcolIDArray[varname]);
                        }
                    }

                    for (k = 0; k < currentblocknames.length; k++) {

                        var backfillstart = currentblocknames[k];
                        var backFill = true;
                        var idToGrow = backfillstart;

                        var currentheight = jQuery("#" + this.id).height();
                        var currentheaderheight = parseInt(jQuery("#" + this.id + " .headerblock").outerHeight());
                        var currentblockspacing = jQuery("#" + yjCurrentMasonry + ".yj-posts").data("blockspacing"); // block spacing                               
                        newinnerheight = currentheight - currentheaderheight;//- currentblockspacing;

                        jQuery("#" + this.id + " .inner").css("height", newinnerheight + "px")


                        minvarname = jQuery("#" + backfillstart).data("startcol");

                        maxvarname = minvarname + jQuery("#" + backfillstart).data("colwidth") - 1;

                        backFillcurrentCol = minvarname;
                        backFillendCol = maxvarname;
                        for (z = backFillcurrentCol; z < backFillendCol; z++) {
                            tmpvarname = "col" + z;
                            if (colIDArray[tmpvarname] == backfillstart) {
                                backFill = false;
                            }
                        }
                        minvarname = "col" + minvarname;
                        minvarname = colIDArray[minvarname];

                        maxvarname = "col" + maxvarname;
                        maxvarname = colIDArray[maxvarname]
                        maxvarname = jQuery(this).attr("id")

                        currentvarname = jQuery(this).attr("id");

                        blocknames = [];
                        blocknamesObj = {};

                        //check to see if already run 
                        if (jQuery("#" + backfillstart).attr("changemyheight")) {
                            backFill = false;
                        } else {
                            for (i = backFillcurrentCol; i <= backFillendCol; i++) {

                                varname = "col" + i;
                                currentcolulength = parseInt(jQuery("#" + colIDArray[varname]).data("ylength"));
                                if (currentvarname == "undefined" || maxvarname == backfillstart || minvarname == backfillstart || ((colIDArray[varname] == backfillstart) && (currentcolulength > (maxheight))) || parseInt(jQuery("#" + minvarname).data("ylength")) > maxheight) {
                                    backFill = false;
                                }

                                if (colIDArray[varname] == idToGrow) {
                                    backFill = false;
                                }
                                if (blocknames.indexOf(colIDArray[varname]) == -1) {

                                    blocknames.push(colIDArray[varname]);
                                    blockpostocheck = [];

                                    for (g = backFillcurrentCol; g <= backFillendCol; g++) {

                                        vara = colIDArray["col" + g];
                                        varb = colIDArray[varname];

                                        if (vara == varb) {
                                            blockpostocheck.push(g);
                                        }
                                    }

                                    blocknamesObj[colIDArray[varname]] = blockpostocheck;

                                }
                            }

                            if (gapfill == true) {
                                //apply backfilling
                                if (backFill == true) {

                                    for (i = 0; i < blocknames.length; i++) {

                                        heightstocheck = jQuery("#" + blocknames[i]).data("ypos");

                                        growstatus = "yes";
                                        if (blocknames[i] == jQuery(this).attr("id")) {
                                            growstatus = "no";
                                        }

                                        if (heightstocheck < heightToGrow) {
                                            heightToGrow = heightstocheck;
                                        }

                                        if (i == blocknames.length - 1) {
                                            newheight = heightToGrow - parseInt(jQuery("#" + idToGrow).attr("data-ypos"));
                                            jQuery("#" + idToGrow).attr("changemyheight", "changed");
                                            jQuery("#" + idToGrow).css("height", newheight + "px");
                                            headerheight = parseInt(jQuery("#" + idToGrow + " .headerblock").outerHeight());
                                            var blockspacing = jQuery("#" + yjCurrentMasonry + ".yj-posts").data("blockspacing"); // block spacing	                            
                                            newinnerheight = newheight - headerheight - blockspacing;
                                            jQuery("#" + idToGrow + " .inner").css("height", newinnerheight + "px")
                                            jQuery("#" + idToGrow).removeAttr("data-ylength");
                                            jQuery("#" + idToGrow).attr("data-ylength", newheight);
                                            heightToGrow = 999999999999999;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                heightArray = [];
                heightArray1 = [];
                heightArrayall = [];
                heightArrayallfinesse = [];

                currentcol = currentcol + colamount;

                var attr = jQuery(this).find(".inner").css('height');
                jQuery("#" + this.id + " .inner").css("height", attr);


                //jQuery(this).data("count", griditemCount);
                jQuery(this).css({
                    'position': 'absolute',
                    'top': ypos + 'px',
                    'left': xpos + 'px',
                    'filter': 'alpha(opacity=100)',
                    'opacity': '1',
                    /*'height' : ylength + 'px',
                     'width' : xwidth + 'px',*/
                    'transition': 'opacity 1s'
                });
            });

            jQuery('#' + yjCurrentMasonry + '.yj-posts').css({
                'height': biggest + 'px',
                'opacity': '1'
            });

            jQuery("#" + yjCurrentMasonry + " .griditem").fadeIn();
        }
    });
}

//Function to find the lowest possible place for the current column
function findlowestpos(heightArrayall, heightArrayallstart, colamount, postobeexcluded, colArray, startpoint) {

    var restart = false;

    heightArrayall = heightArrayall;

    heightArrayallstart = heightArrayallstart;
    colamount = colamount;
    heightArrayfinesse = [];
    postobeexcluded = [];
    postobeexcluded = postobeexcluded;
    availablecols = startpoint + 1;


    var lowestpos = indexOfMax(heightArrayallstart, postobeexcluded);
    var currentlowestpos = lowestpos;

    for (i = lowestpos; i <= lowestpos + colamount - 1; i++) {
        varnameall = "col" + i;
        varlowestpos = "col" + lowestpos;

        if (colArray[varnameall] <= colArray[varlowestpos] + 20) {
            heightArrayfinesse.push(colArray[varnameall]);
        } else
        {
            if (restart == false) {
                postobeexcluded.push(currentlowestpos);
                lowestpos = indexOfMax(heightArrayallstart, postobeexcluded);

                i = lowestpos;
                currentlowestpos = lowestpos;
                if (postobeexcluded.length == startpoint) {
                    restart = true;
                }
            }

        }
    }

    return lowestpos;
}

/**
 * Refined find lowest position
 * 
 * @param {type} heightArrayall
 * @param {type} heightArrayallstart
 * @param {type} colamount
 * @param {type} postobeexcluded
 * @param {type} colArray
 * @param {type} startpoint
 * @returns {Number}
 */
function refinedFindlowestpos(heightArrayall, heightArrayallstart, colamount, postobeexcluded, colArray, startpoint) {
    var tovar = 12 - colamount;
    var lowestarray = [];
    //console.log("tovar " + tovar);
    for (i = 0; i <= tovar; i++) {

        var currentpos = i;
        //console.log("currentpos " + currentpos);
        var currentspot = 0;
        for (o = currentpos; o <= currentpos + colamount - 1; o++) {
            varnameall = "col" + o;
            //console.log(varnameall);
            currentspot = currentspot + colArray[varnameall];
        }
        //console.log(currentspot);
        lowestarray.push(currentspot);
    }
    //console.log(lowestarray);
    var lowestoutput = indexOfMax(lowestarray, "");
    return lowestoutput;
}

//actually min
function indexOfMax(arr, postobeexcluded) {
    if (arr.length === 0) {
        return -1;
    }

    var max = arr[0];
    var maxIndex = 0;

    for (var i = 1; i < arr.length; i++) {
        if (arr[i] < max) {
            if (postobeexcluded.indexOf(i) == -1) {
                maxIndex = i;
                max = arr[i];
            }

        }
    }

    return maxIndex;
}

function actualCols(obj) {
    var colsClass = jQuery(obj).parent('div').attr('class').split(' ').find(isCol);
    var split = colsClass.split('-');
    return parseInt(split[2]);
}

function isCol(col) {
    return col.startsWith("col-sm-");
}

function changeColClass(obj, inc) {
    var cols = actualCols(obj);
    var new_cols;

    if (inc) {
        new_cols = cols + 1;
    } else {
        new_cols = cols - 1;
    }


    jQuery(obj).parent('div').removeClass('col-sm-' + cols.toString()).addClass('col-sm-' + new_cols.toString());
    jQuery(obj).data('cols', new_cols).text(new_cols.toString());

    gridResize();
}