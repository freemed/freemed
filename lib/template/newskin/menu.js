	//----------------------------------------------------------------------------
	// Code to determine the browser and version.
	//----------------------------------------------------------------------------
	
	function Browser() {
	
		var ua, s, i;
	
		this.isIE		= false;	// Internet Explorer
		this.isNS		= false;	// Netscape
		this.version = null;
	
		ua = navigator.userAgent;
	
		s = "MSIE";
		if ((i = ua.indexOf(s)) >= 0) {
			this.isIE = true;
			this.version = parseFloat(ua.substr(i + s.length));
			return;
		}
	
		s = "Netscape6/";
		if ((i = ua.indexOf(s)) >= 0) {
			this.isNS = true;
			this.version = parseFloat(ua.substr(i + s.length));
			return;
		}
	
		// Treat any other "Gecko" browser as NS 6.1.
	
		s = "Gecko";
		if ((i = ua.indexOf(s)) >= 0) {
			this.isNS = true;
			this.version = 6.1;
			return;
		}
	}
	
	var browser = new Browser();
	
	//----------------------------------------------------------------------------
	// Code for handling the menu bar and active button.
	//----------------------------------------------------------------------------
	
	var activeButton = null;
	
	/* [MODIFIED] This code commented out, not needed for activate/deactivate
		 on mouseover.
	
	// Capture mouse clicks on the page so any active button can be
	// deactivated.
	
	if (browser.isIE)
		document.onmousedown = pageMousedown;
	else
		document.addEventListener("mousedown", pageMousedown, true);
// If there is no active button, exit.
	
		if (activeButton == null)
			return;
	
		// Find the element that was clicked on.
	
		if (browser.isIE)
			el = window.event.srcElement;
		else
			el = (event.target.tagName ? event.target : event.target.parentNode);
	
		// If the active button was clicked on, exit.
	
		if (el == activeButton)
			return;
	
		// If the element is not part of a menu, reset and clear the active
		// button.
	
		if (getContainerWith(el, "DIV", "menu") == null) {
			resetButton(activeButton);
			activeButton = null;
		}
	}
	
	[END MODIFIED] */
	
	function buttonClick(event, menuId) {
	
		var button;
	
		// Get the target button element.
	
		if (browser.isIE)
			button = window.event.srcElement;
		else
			button = event.currentTarget;
	
		// Blur focus from the link to remove that annoying outline.
	
		button.blur();
	
		// Associate the named menu to this button if not already done.
		// Additionally, initialize menu display.
	
		if (button.menu == null) {
			button.menu = document.getElementById(menuId);
			if (button.menu.isInitialized == null)
				menuInit(button.menu);
		}
	
		// [MODIFIED] Added for activate/deactivate on mouseover.
	
		// Set mouseout event handler for the button, if not already done.
	
		if (button.onmouseout == null)
			button.onmouseout = buttonOrMenuMouseout;
	
		// Exit if this button is the currently active one.
	
		if (button == activeButton)
			return false;
	
		// [END MODIFIED]
	
		// Reset the currently active button, if any.
	
		if (activeButton != null)
			resetButton(activeButton);
	
		// Activate this button, unless it was the currently active one.
	
		if (button != activeButton) {
			depressButton(button);
			activeButton = button;
		}
		else
			activeButton = null;
	
		return false;
	}
	
	function buttonMouseover(event, menuId) {
	
		var button;
	
		// [MODIFIED] Added for activate/deactivate on mouseover.
	
		// Activates this button's menu if no other is currently active.
	
		if (activeButton == null) {
			buttonClick(event, menuId);
			return;
		}
	
		// [END MODIFIED]
	
		// Find the target button element.
	
		if (browser.isIE)
			button = window.event.srcElement;
		else
			button = event.currentTarget;
	
		// If any other button menu is active, make this one active instead.
	
		if (activeButton != null && activeButton != button)
			buttonClick(event, menuId);
	}
	
	function depressButton(button) {
	
		var x, y;
	
		// Update the button's style class to make it look like it's
		// depressed.
	
		button.className += " menuButtonActive";
	
		// [MODIFIED] Added for activate/deactivate on mouseover.
	
		// Set mouseout event handler for the button, if not already done.
	
		if (button.onmouseout == null)
			button.onmouseout = buttonOrMenuMouseout;
		if (button.menu.onmouseout == null)
			button.menu.onmouseout = buttonOrMenuMouseout;
	
		// [END MODIFIED]
	
		// Position the associated drop down menu under the button and
		// show it.
	
		x = getPageOffsetLeft(button);
		y = getPageOffsetTop(button) + button.offsetHeight;
	
		// For IE, adjust position.
	
		if (browser.isIE) {
			x += button.offsetParent.clientLeft;
			y = y + button.offsetParent.clientTop - 2;
		}
	
		button.menu.style.left = x + "px";
		button.menu.style.top = y + "px";
		
		button.menu.style.visibility = "visible";
	}
	
	function resetButton(button) {
	
		// Restore the button's style class.
	
		removeClassName(button, "menuButtonActive");
	
		// Hide the button's menu, first closing any sub menus.
	
		if (button.menu != null) {
			closeSubMenu(button.menu);
			button.menu.style.visibility = "hidden";
		}
	}
	
	//----------------------------------------------------------------------------
	// Code to handle the menus and sub menus.
	//----------------------------------------------------------------------------
	
	function menuMouseover(event) {
	
		var menu;
	
		// Find the target menu element.
	
		if (browser.isIE)
			menu = getContainerWith(window.event.srcElement, "DIV", "menu");
		else
			menu = event.currentTarget;
	
		// Close any active sub menu.
	
		if (menu.activeItem != null)
			closeSubMenu(menu);
	}
	
	function menuItemMouseover(event, menuId) {
	
		var item, menu, x, y;
	
		// Find the target item element and its parent menu element.
	
		if (browser.isIE)
			item = getContainerWith(window.event.srcElement, "A", "menuItem");
		else
			item = event.currentTarget;
		menu = getContainerWith(item, "DIV", "menu");
	
		// Close any active sub menu and mark this one as active.
	
		if (menu.activeItem != null)
			closeSubMenu(menu);
		menu.activeItem = item;
	
		// Highlight the item element.
	
		item.className += " menuItemHighlight";
	
		// Initialize the sub menu, if not already done.
	
		if (item.subMenu == null) {
			item.subMenu = document.getElementById(menuId);
			if (item.subMenu.isInitialized == null)
				menuInit(item.subMenu);
		}
	
		// [MODIFIED] Added for activate/deactivate on mouseover.
	
		// Set mouseout event handler for the sub menu, if not already done.
	
		if (item.subMenu.onmouseout == null)
			item.subMenu.onmouseout = buttonOrMenuMouseout;
	
		// [END MODIFIED]
	
		// Get position for submenu based on the menu item.
	
		x = getPageOffsetLeft(item) + item.offsetWidth;
		y = getPageOffsetTop(item);
	
		// Adjust position to fit in view.
	
		var maxX, maxY;
	
		if (browser.isNS) {
			maxX = window.scrollX + window.innerWidth;
			maxY = window.scrollY + window.innerHeight;
		}
		if (browser.isIE) {
			maxX = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft) +
				(document.documentElement.clientWidth != 0 ? document.documentElement.clientWidth : document.body.clientWidth);
			maxY = Math.max(document.documentElement.scrollTop, document.body.scrollTop) +
				(document.documentElement.clientHeight != 0 ? document.documentElement.clientHeight : document.body.clientHeight);
		}
		maxX -= item.subMenu.offsetWidth;
		maxY -= item.subMenu.offsetHeight;
	
		if (x > maxX)
			x = Math.max(0, x - item.offsetWidth - item.subMenu.offsetWidth
				+ (menu.offsetWidth - item.offsetWidth));
		y = Math.max(0, Math.min(y, maxY));
	
		// Position and show the sub menu.
	
		item.subMenu.style.left = x + "px";
		item.subMenu.style.top	= y + "px";
		item.subMenu.style.visibility = "visible";
	
		// Stop the event from bubbling.
	
		if (browser.isIE)
			window.event.cancelBubble = true;
		else
			event.stopPropagation();
	}
	
	function closeSubMenu(menu) {
	
		if (menu == null || menu.activeItem == null)
			return;
	
		// Recursively close any sub menus.
	
		if (menu.activeItem.subMenu != null) {
			closeSubMenu(menu.activeItem.subMenu);
			menu.activeItem.subMenu.style.visibility = "hidden";
			menu.activeItem.subMenu = null;
		}
		removeClassName(menu.activeItem, "menuItemHighlight");
		menu.activeItem = null;
	}
	
	// [MODIFIED] Added for activate/deactivate on mouseover. Handler for mouseout
	// event on buttons and menus.
	
	function buttonOrMenuMouseout(event) {
	
		var el;
	
		// If there is no active button, exit.
	
		if (activeButton == null)
			return;
	
		// Find the element the mouse is moving to.
	
		if (browser.isIE)
			el = window.event.toElement;
		else if (event.relatedTarget != null)
				el = (event.relatedTarget.tagName ? event.relatedTarget : event.relatedTarget.parentNode);
	
		// If the element is not part of a menu, reset the active button.
	
		if (getContainerWith(el, "DIV", "menu") == null) {
			resetButton(activeButton);
			activeButton = null;
		}
	}
	
	// [END MODIFIED]
	
	//----------------------------------------------------------------------------
	// Code to initialize menus.
	//----------------------------------------------------------------------------
	
	function menuInit(menu) {
	
		var itemList, spanList;
		var textEl, arrowEl;
		var itemWidth;
		var w, dw;
		var i, j;
	
		// For IE, replace arrow characters.
	
		if (browser.isIE) {
			menu.style.lineHeight = "2.5ex";
			spanList = menu.getElementsByTagName("SPAN");
			for (i = 0; i < spanList.length; i++)
				if (hasClassName(spanList[i], "menuItemArrow")) {
					spanList[i].style.fontFamily = "Webdings";
					spanList[i].firstChild.nodeValue = "4";
				}
		}
	
		// Find the width of a menu item.
	
		itemList = menu.getElementsByTagName("A");
		if (itemList.length > 0)
			itemWidth = itemList[0].offsetWidth;
		else
			return;
	
		// For items with arrows, add padding to item text to make the
		// arrows flush right.
	
		for (i = 0; i < itemList.length; i++) {
			spanList = itemList[i].getElementsByTagName("SPAN");
			textEl	= null;
			arrowEl = null;
			for (j = 0; j < spanList.length; j++) {
				if (hasClassName(spanList[j], "menuItemText"))
					textEl = spanList[j];
				if (hasClassName(spanList[j], "menuItemArrow"))
					arrowEl = spanList[j];
			}
			if (textEl != null && arrowEl != null)
				textEl.style.paddingRight = (itemWidth
					- (textEl.offsetWidth + arrowEl.offsetWidth)) + "px";
		}
	
		// Fix IE hover problem by setting an explicit width on first item of
		// the menu.
	
		if (browser.isIE) {
			w = itemList[0].offsetWidth;
			itemList[0].style.width = w + "px";
			dw = itemList[0].offsetWidth - w;
			w -= dw;
			itemList[0].style.width = w + "px";
		}
	
		// Mark menu as initialized.
	
		menu.isInitialized = true;
	}
	
	//----------------------------------------------------------------------------
	// General utility functions.
	//----------------------------------------------------------------------------
	
	function getContainerWith(node, tagName, className) {
	
		// Starting with the given node, find the nearest containing element
		// with the specified tag name and style class.
	
		while (node != null) {
			if (node.tagName != null && node.tagName == tagName &&
					hasClassName(node, className))
				return node;
			node = node.parentNode;
		}
	
		return node;
	}
	
	function hasClassName(el, name) {
	
		var i, list;
	
		// Return true if the given element currently has the given class
		// name.
	
		list = el.className.split(" ");
		for (i = 0; i < list.length; i++)
			if (list[i] == name)
				return true;
	
		return false;
	}
	
	function removeClassName(el, name) {
		// ### This function was fixed by Nigel Swinson, Jan 2003.  It didn't work
		// in IE 5.0 as it used a "push()" Array method, which isn't available in IE 5.
	
		var i, curList, newList;
	
		// No class name to remove from, return immediately
		if (el.className == null)
			return;
	
		// Remove the given class name from the element's className property.
	
		// Split the list of classes into an array
		newList = new String();
		curList = el.className.split(" ");
		// Cycle through the list 
		for (i = 0; i < curList.length; i++)
			if (curList[i] != name) {
				newList = newList + ' ' + curList[i];
			}
		el.className = newList;
	}
	
	function getPageOffsetLeft(el) {
	
		var x;
	
		// Return the x coordinate of an element relative to the page.
	
		x = el.offsetLeft;
		if (el.offsetParent != null)
			x += getPageOffsetLeft(el.offsetParent);
	
		return x;
	}
	
	function getPageOffsetTop(el) {
	
		var y;
	
		// Return the x coordinate of an element relative to the page.
	
		y = el.offsetTop;
		if (el.offsetParent != null)
			y += getPageOffsetTop(el.offsetParent);
	
		return y;
	}