//分页控件单击事件
function pageClick(callBack) {
    $("div.pagin a").click(function () {
        // $(".page_content").css('pointer-events','none');
        var id = $(this).attr("id");
        if ($(this).attr("id") == pageIndex) {
            return;
        }
        if (id == "prev") {
            pageIndex--;
        }
        else if (id == "next") {
            pageIndex++;
        }
        else {
            pageIndex = $(this).attr("id");
        }
        pageIndex = parseInt(pageIndex);

        if (pageIndex < 1 || pageIndex > pageCount) {
            return;
        }
        // callBack();
    });
}

//刷新分页控件
function pageRefresh(callBack) {
          url = url.replace(/%2C/g, ",");
	var forUrl = url;
	var reUrl = url;
    //$("#pagedes").html("共<a>" + recordCount + "</a>条数据");
    if (recordCount > 0) {
        if (recordCount % pageSize == 0) {
            pageCount = recordCount / pageSize;
        }
        else {
            pageCount = (recordCount - recordCount % pageSize) / pageSize + 1;
        }
        pageIndex = parseInt(pageIndex);
        if (pageIndex < 1) {
            pageIndex = 1;
        }
        else if (pageIndex > pageCount) {
            pageIndex = pageCount;
        }
        var pagelist = "";
        if (pageIndex == 1) {
            pagelist = "<span  class='prev-disabled'>Prev page<b></b></span> " + "<a id='1'>1</a> ";
        }
        else {
            pagelist = "<a id='prev' class='prev' href='"+reUrl+(pageIndex - 1)+"'>Prev page<b></b></a> " + "<a id='1' href='"+reUrl+"1'>1</a> ";
        }
        if (pageIndex - 2 > 2) {
            pagelist += "<span class='text'>...</span> ";
        }
        if (1 < pageIndex - 2 && pageIndex - 2 < pageCount) {

			url = url+(pageIndex - 2);

            pagelist += "<a class='2' id='" + (pageIndex - 2) + "' href='"+url+"'>" + (pageIndex - 2) + "</a> ";
        }
        if (1 < pageIndex - 1 && pageIndex - 1 < pageCount) {
			//alert(url);
			url = forUrl+(pageIndex - 1);
            pagelist += "<a class='1' id='" + (pageIndex - 1) + "' href='"+url+"'>" + (pageIndex - 1) + "</a> ";
        }
        if (1 < pageIndex && pageIndex < pageCount) {

            pagelist += "<a id='" + pageIndex + "'>" + pageIndex + "</a> ";
        }

        for (var i = pageIndex + 1; i < pageCount && i <= pageIndex + 2; i++) {
            pagelist += "<a class='3' id='" + i + "' href='"+forUrl+i+"'>" + i + "</a> ";
        }
        if (pageIndex + 3 < pageCount) {
            pagelist += "<span class='text'>...</span> ";
        }
        if (pageIndex < pageCount) {
            pagelist += "<a id='" + pageCount + "' href='"+reUrl+pageCount+"'>" + pageCount + "</a> " + "<a id='next' href='"+reUrl+(pageIndex + 1)+"' class='next'	>Next page<b></b></a>";
        }
        else if (pageIndex > 1) {
            pagelist += "<a id='" + pageCount + "'>" + pageCount + "</a> " + "<span class='next-disabled'>Next page<b></b></span>";
        }
        else {
            pagelist += "<span class='next-disabled'>Next page<b></b></span>";
        }
        $("#page").html(pagelist);
        $("#" + pageIndex).addClass("cur");
        pageClick(callBack);
    }
    else {
        $("#page").empty();
    }
}



