// 判斷列表中，是否存在一個選項
function jsSelectIsExitItem(objSelect, objItemValue)
{
	var isExit = false;
	for (var i = 0; i < objSelect.options.length; i++)
	{
		if (objSelect.options[i].value == objItemValue)
		{
			isExit = true;
			break;
		}
	}
	return isExit;
}
// 回傳列表中所有選項
function jsAllItems(objSelect)
{
	myArr = new Array();
	for (var i = 0; i < objSelect.options.length; i++)
		myArr.push(objSelect.options[i].value);
	return myArr;
}
// 加入一個選項
function jsAddItemToSelect(objSelect, objItemText, objItemValue)
{
	//判斷是否存在
	if (jsSelectIsExitItem(objSelect, objItemValue))
		alert("已經有同值的選項");
	else
    {
		var varItem = new Option(objItemText, objItemValue);
		objSelect.options.add(varItem);
	}
}
// 將一個選項刪除
function jsRemoveItemFromSelect(objSelect, objItemValue)
{
	//判斷是否存在
	if (jsSelectIsExitItem(objSelect, objItemValue))
	{
		for (var i = 0; i < objSelect.options.length; i++)
		{
			if (objSelect.options[i].value == objItemValue)
			{
				objSelect.options.remove(i);
				break;
			}
		}
    }
    else
		alert("不存在該選項");
}

// 刪除選中的選項
function jsRemoveSelectedItemFromSelect(objSelect)
{
    var length = objSelect.options.length - 1;
    for(var i = length; i >= 0; i--)
    {
		if(objSelect[i].selected == true)
			objSelect.options[i] = null;
    }
}

// 將選項中 value 為 objItemValue 的選項改為 objItemText
function jsUpdateItemToSelect(objSelect, objItemText, objItemValue)
{
	//判斷是否存在
	if (jsSelectIsExitItem(objSelect, objItemValue))
	{
		for (var i = 0; i < objSelect.options.length; i++)
		{
			if (objSelect.options[i].value == objItemValue)
			{
				objSelect.options[i].text = objItemText;
				break;
			}
		}
	}
	else
		alert("不存在該選項");
}
  
// 將內容為 objItemText 的選項改為選取
function jsSelectItemByValue(objSelect, objItemText)
{
	//判斷是否存在
	var isExit = false;
	for (var i = 0; i < objSelect.options.length; i++)
	{
		if (objSelect.options[i].text == objItemText)
		{
			objSelect.options[i].selected = true;
			isExit = true;
			break;
		}
    }

	if (!isExit)
		alert("不存在該選項");
}
// 交換
function swap(obj1, obj2)
{
	var value, text, order;
	text = obj2.text;
	value = obj2.value;
	obj2.text = obj1.text;
	obj2.value = obj1.value;
	obj1.text = text;
	obj1.value = value;
}
// 上移
function jsMoveUp(view)
{
	var i;
	for(i=0; i < view.length; i++)
	{
		if(view[i].selected && view.length > 1 && i!=0)
		{
			swap(view[i], view[i-1]);
			view[i-1].selected = true;
			view[i].selected = false;
		}
	}
}
// 下移
function jsMoveDown(view)
{
	var i;
	for(i=view.length-1; i >= 0 ; i--)
	{
		if(view[i].selected && view.length > 1 && i < view.length - 1)
		{
			swap(view[i], view[i+1]);
			view[i+1].selected=true;
			view[i].selected=false;
		}
	}
}
