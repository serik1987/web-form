/////////////////////////////////////////////////////////////
//                                                         //
//   /media/system/js/order_button.js                      //
//                                                         //
//   Plugin for placing Order buttons to the website       //
//                                                         //
//   (C) Sergei Kozhukhov, 2016                            //
//                                                         //
//   Requires MoTools to be previously installed           //
//                                                         //
/////////////////////////////////////////////////////////////


/* looks for last double quote in the string */
function lookForLastQuote(str){
	var i=-1, j;
	while ((j=str.indexOf('"',i))!==-1){
		i=j+1;
	}
	return i;
}


/* Extracts all necessary information from the previous list and puts it into appropriate fields of Order form. Makes this form visible */
function showDialogBox(eventInfo){
	var size = [];
	var good = "";
	var color = "";
	var sizeRegex = /(\d+)\s*[хХxX]\s*(\d+)\s*[хХxX]\s*(\d+)/gi;
		
	// size parsing
	var eventElement = $$(eventInfo.target);
	eventElement.getPrevious().getChildren()[0].each(function(infoElement){
		if(infoElement.getChildren()[0].get('text').startsWith("Размеры")){
			var size1 = sizeRegex.exec(infoElement.get("text"));
			size[0] = parseInt(size1[1]);
			size[1] = parseInt(size1[2]);
			size[2] = parseInt(size1[3]);
			if (isNaN(size[0]) || isNaN(size[1]) || isNaN(size[2])){
				console.log("ОШИБКА! Эта кнопка не работает, поскольку в поле \"Размеры\" не заданы три целых числа, разделённых знаком х."+
					" В соответствующий материал необходимо внести изменения.");
				return;
			}
		}
	});
	if (size.length===0){
		console.log("ОШИБКА! Перед этой кнопкой должен быть расположен немаркированный список, один из пунктов которого должен начинаться со слова Размеры"+
		" Это слово должно быть выделено полужирным, а в самом пункте должны присутствовать три целых числа, разделённых знаком x. "+
		"Необходимо внести изменения в соответствующий материал");
		return;
	}
	
	// good and color parsing
	var tableElement = eventElement;
	do {
		tableElement=tableElement.getParent();
		if (tableElement===null){
			console.log("Error. <table> not found");
			return;
		}
		if (tableElement[0].tagName==="TABLE"){
			break;
		}
	} while (true);
	var total_header = tableElement.getPrevious()[0].get("text");
	var splitter = lookForLastQuote(total_header);
	good=total_header.substr(0,splitter);
	color=total_header.substr(splitter+1);
	
	var header = eventElement[0].get('text');
	
	// Putting in all parsed values
	$$("#good-name")[0].set("value",good);
	var sizeString = size[0]+"x"+size[1]+"x"+size[2]+" мм";
	$$("#good-size")[0].set("placeholder",sizeString+". Напишите желаемые размеры");
	$$("#good-size-default")[0].set("value",sizeString);
	$$("#good-color")[0].set("value",color);
	$$(".order-form header")[0].set('text',header);
	
	// Setting element size and making the element visible
	$$(".order-form")[0].setStyle("height",document.body.scrollHeight+"px");
	$$(".order-form form").setStyle("margin-top",$$("body").getScroll()[0].y+100+"px");
	$$(".order-form")[0].setStyle("visibility","visible");
}


/* Checks whether all fields in the form filled valid */
function isFilledValid(){
	var problem_element=null;
	
	$$("#order-form-body input[type=text]:invalid").each(function(el){
		if (problem_element===null){
			problem_element=el;
		}
	});
	if (problem_element!==null){
		problem_element.focus();
		showErrorMessage("Вы неправильно заполнили это поле");
		return false;
	}
	return true;
}



/* Sends order request via AJAX/JSON */
var jsonInfo;
function submitOrder(){
	var helper=$$("#good-size")[0].get('value');
	
	var submitInfo = {
		client_name: $$("#client-name")[0].get('value'),
		client_phone: $$("#client-phone")[0].get('value'),
		client_mail: $$("#client-mail")[0].get('value'),
		good_name: $$("#good-name")[0].get('value'),
		good_size: helper!==""?helper:$$("#good-size-default")[0].get('value'),
		good_color: $$("#good-color")[0].get('value'),
		order_comments: $$("#order-comments")[0].get('value')
	};
	
	new Request.JSON({
		url: "/orders/order.php",
		onSuccess: function(responseObject,responseText){
			console.log("Response success");
			console.log(responseObject);
			if (responseObject.code!==0){
				showErrorMessage(responseObject.message);
			} else {
				showSuccessMessage(responseObject.message);
			}
		},
		onFailure: function(){
			showErrorMessage("Ошибка передачи данных");
		}
	}).post(submitInfo);
}

/* Showing two small messages: success and error */
function showSuccessMessage(msg) {showMessage(msg,"success-box");}
function showErrorMessage(msg) {showMessage(msg,"error-box");}
function showMessage(msg,image_class){
	var msgBox = $$("#msg-box")[0];
	msgBox.set("class","");
	msgBox.set("text",msg);
	msgBox.addClass(image_class);
	msgBox.addClass("active");
	setTimeout(function(){
		msgBox.removeClass("active");
	},2500);
}




/* Places the button where it is required */
window.onload = function(){
	$$("article h3+table td:nth-child(2)").each(function(parentElement){
		
		var buttonElement = new Element('<div></div>');
		buttonElement.appendText("Сделать заказ");
		buttonElement.addClass("order-button");
		buttonElement.set("href","#");
		
		buttonElement.addEvent('click',showDialogBox);
		
		buttonElement.inject(parentElement);
	});
	
	$$(".order-form")[0].addEvent("click",function(){
		$$(".order-form")[0].setStyle("visibility","hidden");
	});
	
	$$(".order-form form").addEvent("click",function(eventInfo){
		return false;
	});
	
	$$("#order-submit").addEvent("click",function(eventInfo){
		$$("#order-submit")[0].blur();		
		if (!isFilledValid()) return;
		$$(".order-form")[0].setStyle("visibility","hidden");
		submitOrder();
	});
}