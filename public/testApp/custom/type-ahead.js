/**
 * TypeAhead
 *
 * @constructor
 * @param {HTMLInputElement} element
 * @param {Array} candidates
 */
var TypeAhead = function (element, candidates, opts) {
    var typeAhead = this;
    opts = opts || {};

    typeAhead.element = element;

    typeAhead.candidates = candidates || [];

    typeAhead.list = new TypeAheadList(typeAhead);

    this.minLength = opts.hasOwnProperty('minLength') ? opts.minLength : 3;

    typeAhead.limit = opts.hasOwnProperty('limit') ? opts.limit : 5;

    typeAhead.onMouseDown = opts.hasOwnProperty('onMouseDown') ? opts.onMouseDown : function(){};

    typeAhead.onKeyDown = opts.hasOwnProperty('onKeyDown') ? opts.onKeyDown : function(){};

    typeAhead.fulltext = opts.hasOwnProperty('fulltext') ? opts.fulltext : false;

    typeAhead.scrollable = opts.hasOwnProperty('scrollable') ? opts.scrollable : false;

    typeAhead.callback = opts.hasOwnProperty('callback') ? opts.callback : function(){};

    typeAhead.query = '';

    typeAhead.selected = null;
	
	//FIX
	typeAhead.substCandidate = ''; // used to remember last auto substitution in textfield after a single match
								   // as long as the substCandidate is equal to a single match
								   // no substitution is done again
    typeAhead.list.draw();

    typeAhead.element.addEventListener('keyup', function (event) {
        typeAhead.handleKeyUp.call(typeAhead, event.keyCode);
    }, false);

    typeAhead.element.addEventListener('keydown', function (event) {
        typeAhead.handleKeyDown.call(typeAhead, event.keyCode) && event.preventDefault();
    });

    typeAhead.element.addEventListener('focus', function () {
        typeAhead.handleFocus.call(typeAhead);
    });

    typeAhead.element.addEventListener('blur', function () {
        typeAhead.handleBlur.call(typeAhead);
    });

    typeAhead.update = function(candidates){
      this.candidates = candidates;
      typeAhead.handleKeyUp.call(typeAhead);
    }

    return typeAhead;
};

/**
 * Key up event handler
 *
 * @param {Integer} keyCode
 */
TypeAhead.prototype.handleKeyUp = function (keyCode) {
    if (keyCode === 13 || keyCode === 38 || keyCode === 40) {
        return;
    }

    this.query = this.filter(this.element.value);

    this.list.clear();

    if (this.query.length < this.minLength) {
        this.list.draw();
        return;
    }

    var typeAhead = this;
    this.getCandidates(function (candidates) {
        for (var i = 0; i < candidates.length; i++) {
            typeAhead.list.add(candidates[i]);
            if (typeAhead.limit !== false && i === typeAhead.limit) {
                break;
            }
        }
        typeAhead.list.draw();
    });
};

/**
 * Key down event handler
 *
 * @param {Integer} keyCode
 * @return {Boolean} Whether event should be captured or not
 */
TypeAhead.prototype.handleKeyDown = function (keyCode) {
    if (keyCode === 13 && !this.list.isEmpty()) {
        this.value(this.list.items[this.list.active]);
        this.list.hide();
        this.onKeyDown(this.list.items[this.list.active]);
        return true;
    }

    if (keyCode === 38) {
        this.list.previous();
        return true;
    }

    if (keyCode === 40) {
        this.list.next();
        return true;
    }

    return false;
};

/**
 * Input blur event handler
 */
TypeAhead.prototype.handleBlur = function () {
    this.list.hide();
};

/**
 * Input focus event handler
 */
TypeAhead.prototype.handleFocus = function () {
    if (!this.list.isEmpty()) {
        this.list.show();
    }
};

/**
 * Filters values before running matcher
 *
 * @param {string} value
 * @return {Boolean}
 */
TypeAhead.prototype.filter = function (value) {
    value = value.toLowerCase();
    return value;
};

/**
 * Compares query to candidate
 *
 * @param {string} candidate
 * @return {Boolean}
 */
TypeAhead.prototype.match = function (candidate) {
	q = this.query.trim(); // FIX query is prepared by getCandidates, but trailing whitespace is removed here for the match

    if(this.fulltext && q.length > 1){	// bij fulltext toch prefix match op eerste char	
        return candidate.indexOf(q) > -1;
    }
	else { 
		if ( q.charAt(0) == "'"  ) {
			return ( 	candidate.indexOf(q) === 0 
			);
		} else { // also try to match leading ' for s Gravenhage etc
			if( q.length > 0 )
				return (	candidate.indexOf(q) === 0	||
							candidate.indexOf("'" + q) === 0
				);
			else return false;
		}
	}
};

/**
 * Sets the value of the input
 *
 * @param {string|Object} value
 */
TypeAhead.prototype.value = function (value) {
	// FIX
	// remove Postcode
	value = value.replace(/^[1-9][0-9]+ /,'');
	// this is a substitution
	this.substCandidate = value;

    this.selected = value;

	
    this.element.value = this.getItemValue(value);
//console.log("Value " + this.element.value);
//console.log(this.element);
    if (document.createEvent) {
        var e = document.createEvent('HTMLEvents');
        e.initEvent('change', true, false);
        this.element.dispatchEvent(e);
    } else {
        this.element.fireEvent('onchange');
    }
    this.callback(value);
};

/**
 * Gets the candidates
 *
 * @param {function} callback
 */
TypeAhead.prototype.getCandidates = function (callback) {
	//console.log('Query: + ' + this.query);
	//console.log('select ' + this.selected);
	//console.log('subst ' + this.substCandidate);
	var that = this; // for functions in Closure
	// many FIXes by Han
	newQuery = this.query.replace(/^\s+/,''); // trim leading whitespace
	newQuery = newQuery.replace(/\s+/g,' '); // no double spaces
	c = newQuery.charAt(0);
	if ( c == '`' || c == ',' || c == '.' || c == '"' ) // handle first , . " or backtick as single quote
		newQuery = newQuery.replace(c,"'");
	if ( newQuery != this.query) {
		this.query = newQuery;
		this.element.value = newQuery; // put result in the text field of the user	
	}
	var tai = U.typeAheadInstance;
	
	zip = U.chkZipCode(this.query); // zipcode in front of query?
	if ( zip == '') this.candidates = tai.deleteZipFromList();
		
	
	if ( zip != '' && tai.lastZip != zip) {
		this.candidates = tai.deleteZipFromList();
	//console.log('YYYY ' + this.query);
		pattern = new RegExp('^[1-9][0-9]+[^0-9\s]');
		newQ = this.query;
		// do not accept anything else but space after zipcode
		if ( pattern.test(this.query) )
			newQ = this.query.replace(/^[1-9][0-9]+[^0-9\s]/, zip);
		if ( newQ != this.query ) {
			this.element.value = this.query = newQ; // fix input field too
		}

		tai.getZipNames(zip, function(list) {
			// Ajax call to get the names for the zipcode; wait for async callback to handle the stuff
			that.candidates = list;		
			handle(callback);
		});
	} else { // no zipcode or same zipcode in front; act normal
		handle(callback);
	}

	function handle(callback) {
		that.element.blur(); // remove focus; be deaf a little while for type in ....
		
		if ( that.element.value.trim().length == 1 ) {
			that.substCandidate = '';
		}
		var items = [];
		for (var i = 0; i < that.candidates.length; i++) {
			var candidate = that.getItemValue(that.candidates[i]);
			if (that.match(that.filter(candidate))) {
				items.push(that.candidates[i]);
			}
		}
		var matchCandidate;
		if ( items.length == 1 ) { // 1 match handling
			matchCandidate = items[0];
			if ( matchCandidate != that.substCandidate) { // no running substitution
				if ( ! /^[1-9]$/.test(that.element.value) ) { // anything except a single number in the text field
					// put match in textField
					x = matchCandidate.replace(/^[1-9][0-9]+ /,'');
					that.substCandidate = that.element.value = that.selected = items[0] = that.query = x;
					setTimeout(function() {
						callback(items);
						that.element.focus();
					}, 1000);
					return; // don't fall down in the code
				}
				// 1 match + single number; actually only needed for 1e exloermond match only!
				oldVal = that.element.value;
				that.element.focus(); // type in enabled; what's going to happen? check later....
				setTimeout ( function() {
					if ( that.element.value == oldVal ) {
						that.substCandidate = that.element.value = that.selected = that.query = matchCandidate;
						callback(items);
					}
				}, 2000);
				return; // don't fall down in the code
			}
			// we keep substCandidate! User can backspace now without substitute loop; above test will fail
		} else {
			that.substCandidate = ''; // 0 or >1 matches; reset
		}
		// defaults, when not returned earlier
		that.element.focus();
		callback(items);
	}
};

/**
 * Extracts the item value, override this method to support array of objects
 *
 * @param {string} item
 * @return {string}
 */
TypeAhead.prototype.getItemValue = function (item) {
    return item;
};

/**
 * Highlights the item
 *
 * @param {string} item
 * @return {string}
 */
TypeAhead.prototype.highlight = function (item) {
    var regExp;
    if (this.fulltext) {
        regExp = '(' + this.query + ')';
    } else {
        regExp = '^(' + this.query + ')';
    }
    return this.getItemValue(item).replace(new RegExp(regExp, 'ig'), function ($1, match) {
        return '<strong>' + match + '</strong>';
    });
};

/**
 * TypeAheadList
 *
 * @constructor
 * @param {TypeAhead} typeAhead
 */
var TypeAheadList = function (typeAhead) {
    var typeAheadList = this;

    typeAheadList.typeAhead = typeAhead;

    typeAheadList.items = [];

    typeAheadList.active = 0;

	tmpElem = document.createElement('div');	// FIX by Han; make container first
	tmpElem.id = "typeAheadListContainer";
	typeAheadList.element = document.createElement('ul');
	typeAheadList.element.className = "typeAheadList";
	typeAheadList.element.id = "typeAheadList";
	tmpElem.appendChild(typeAheadList.element);
	typeAhead.element.parentNode.insertBefore(tmpElem, typeAhead.element.nextSibling);

/* original code
    //typeAheadList.element = document.createElement('ul'); 
	typeAheadList.element = document.createElement('div');
	typeAheadList.element.className = "typeAheadList";
	typeAheadList.element.id = "typeAheadList";
    typeAhead.element.parentNode.insertBefore(typeAheadList.element, typeAhead.element.nextSibling); */

    return typeAheadList;
};

/**
 * Shows the list
 */
TypeAheadList.prototype.show = function () {
    this.element.style.display = 'block';
};

/**
 * Hides the list
 */
TypeAheadList.prototype.hide = function () {
    this.element.style.display = 'none';
};

/**
 * Adds an item to the list
 *
 * @param {string|Object} item
 */
TypeAheadList.prototype.add = function (item) {
    this.items.push(item);
};

/**
 * Clears the list
 */
TypeAheadList.prototype.clear = function () {
    this.items = [];
	this.active = 0;

};

/**
 * Whether the list is empty or not
 *
 * @return {Boolean}
 */
TypeAheadList.prototype.isEmpty = function () {
    return this.element.children.length === 0;
};

/**
 * Renders the list
 */
TypeAheadList.prototype.draw = function () {
    this.element.innerHTML = '';

    if (this.items.length === 0) {
        this.hide();
        return;
    }

    for (var i = 0; i < this.items.length; i++) {
        this.drawItem(this.items[i], this.active === i);
    }

    if (this.typeAhead.scrollable) {
        this.scroll();
    }

    this.show();
};

/**
 * Renders a list item
 *
 * @param {string|Object} item
 * @param {Boolean} active
 */
TypeAheadList.prototype.drawItem = function (item, active) {

	var li = document.createElement('li'), // FIX by Han div; was li; set back again
        a = document.createElement('a');

    if (active) {
        li.className += ' active';
    }

    a.innerHTML = this.typeAhead.highlight(item);
    li.appendChild(a);
    this.element.appendChild(li);

    var typeAheadList = this;
    li.addEventListener('mousedown', function () {
        typeAheadList.handleMouseDown.call(typeAheadList, item);
    });
};

/**
 * Mouse down event handler
 *
 * @param {string|Object} item
 */
TypeAheadList.prototype.handleMouseDown = function (item) {
    this.typeAhead.value(item);
    this.typeAhead.onMouseDown(item);
    this.clear();
    this.draw();
};

/**
 * Move the active flag to the specified index
 */
TypeAheadList.prototype.move = function (index) {
    this.active = index;
    this.draw();
};

/**
 * Move the active flag to the previous item
 */
TypeAheadList.prototype.previous = function () {
    this.move(this.active === 0 ? this.items.length - 1 : this.active - 1);
};

/**
 * Move the active flag to the next item
 */
TypeAheadList.prototype.next = function () {
    this.move(this.active === this.items.length - 1 ? 0 : this.active + 1);
};

/**
 * Adjust the scroll position to keep the active item into the visible area of the list
 */
TypeAheadList.prototype.scroll = function () {
  if (this.isEmpty()) {
      return;
  }

  var item = this.element.children[this.active],
      list = this.element;

  if (item.offsetTop + item.offsetHeight >= list.scrollTop + list.offsetHeight) {
      list.scrollTop = item.offsetTop + item.offsetHeight - list.offsetHeight;
      return;
  }

  if (item.offsetTop < list.scrollTop) {
      list.scrollTop = item.offsetTop;
  }
};

/**
 * Export TypeAhead for Browserify
 */
//module.exports = TypeAhead
