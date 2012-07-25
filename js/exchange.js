

var selected = "";

var buy_last = 2, buy_llast = -1;
var sell_last = 2, sell_llast = -1;













function set_field(type, field, value) {
	
	if (no_set_field) {
		return;
	}
	
	if (selected == "") {
		return;
	}
	
	if (type == "") {
		type = selected;
	}
	
	if (type == "buy") {
		slc_amount = document.getElementById("buy_slc_amount").value;
		price = document.getElementById("buy_price").value;
		cur_amount = document.getElementById("buy_cur_amount").value;
		
		if (field) {
			if (field != buy_last) {
				buy_llast = buy_last;
				buy_last = field;
			}
			
			if (value) {
				if (field == 1) {
					slc_amount = value;
					document.getElementById("buy_slc_amount").value = nice_format(value, 4);
				} else if (field == 2) {
					price = value;
					document.getElementById("buy_price").value = nice_format(value, 8);
				} else if (field == 3) {
					cur_amount = value;
					document.getElementById("buy_cur_amount").value = nice_format(value, 8);
				}
			}
		}
		
		if ((buy_last == 1 && buy_llast == 3) || (buy_last == 3 && buy_llast == 1)) {
			mprice = cur_amount / slc_amount;
			document.getElementById("buy_price").value = nice_format(mprice, 8);
		} else if ((buy_last == 2 && buy_llast == 3) || (buy_last == 3 && buy_llast == 2)) {
			slc_amount = cur_amount / price;
			document.getElementById("buy_slc_amount").value = nice_format(Math.floor(slc_amount * 10000) / 10000, 4);
		} else if ((buy_last == 1 && buy_llast == 2) || (buy_last == 2 && buy_llast == 1)) {
			cur_amount = slc_amount * price;
			document.getElementById("buy_cur_amount").value = nice_format(cur_amount, 8);
		}
		
		document.getElementById("buy_fee").innerHTML = nice_format(cur_amount * 1.004, 8);
		document.getElementById("buy_remaining").innerHTML = nice_format(Math.floor((c_balance - cur_amount * 1.004) * 100000000) / 100000000, 8);
	} else {
		slc_amount = document.getElementById("sell_slc_amount").value;
		price = document.getElementById("sell_price").value;
		cur_amount = document.getElementById("sell_cur_amount").value;
		
		if (field) {
			if (field != sell_last) {
				sell_llast = sell_last;
				sell_last = field;
			}
			
			if (value) {
				if (field == 1) {
					slc_amount = value;
					document.getElementById("sell_slc_amount").value = nice_format(value, 4);
				} else if (field == 2) {
					price = value;
					document.getElementById("sell_price").value = nice_format(value, 8);
				} else if (field == 3) {
					cur_amount = value;
					document.getElementById("sell_cur_amount").value = nice_format(value, 8);
				}
			}
		}
		
		if ((sell_last == 1 && sell_llast == 3) || (sell_last == 3 && sell_llast == 1)) {
			mprice = cur_amount / slc_amount;
			document.getElementById("sell_price").value = nice_format(mprice, 8);
		} else if ((sell_last == 2 && sell_llast == 3) || (sell_last == 3 && sell_llast == 2)) {
			slc_amount = cur_amount / price;
			document.getElementById("sell_slc_amount").value = nice_format(slc_amount, 4);
		} else if ((sell_last == 1 && sell_llast == 2) || (sell_last == 2 && sell_llast == 1)) {
			cur_amount = slc_amount * price;
			document.getElementById("sell_cur_amount").value = nice_format(cur_amount, 8);
		}
		
		document.getElementById("sell_fee").innerHTML = nice_format(cur_amount * 0.996, 8);
		document.getElementById("sell_remaining").innerHTML = nice_format(s_balance - slc_amount, 4);
	}
}

function nice_format(value, decimals) {
	precision = 1;
	
	for (i = 0; i < decimals; i++) precision *= 10;
	
	value = to_fixed(Math.round(value * precision) / precision);
	
	return value;
}

String.prototype.replaceAt=function(index, char) {
	return this.substr(0, index) + char + this.substr(index+char.length);
}

function to_fixed(x) {
	if (x > 0 && x < 1) {
		x += 1;
		
		var num = String(x);
		num = num.replaceAt(0, '0');
		
		return num;
	}
	if (x < 0 && x > -1) {
		x -= 1;
		
		var num = String(x);
		num = num.replaceAt(1, '0');
		
		return num;
	}
	return x;
}