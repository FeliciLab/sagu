dojo.declare("Miolo.Box",null,
{
	constructor: function() {
	    this.boxMoving = false;
        //control move box (drag)
        this.boxToMove = null;
        //control how box will be moving
        this.boxPositions = new Array(2);
        this.onMouseMoveHandler = miolo.associateObjWithEvent(this,'boxPosition');
	},
    showBox: function( event, div ) {
		var div = Event.element(event);
        var id = div.id.substring( 4 );
        var aux = miolo.getElementById(id);
        aux.style.display = '';
        div.style.display = 'none';
		Event.stopObserving(document,'mousemove',this.onMouseMoveHandler);
    },
    closeBox: function( e, boxId ) {
		var box = miolo.getElementById(boxId);
        var div  = miolo.getElementById('min_' + boxId);
		Event.stopObserving(document,'mousemove',this.onMouseMoveHandler);
        if ( div == null )
        {
            var cont = miolo.getElementById('m-container-minbar');
            if ( ! cont )
            {
                return false;
            }
            div  = document.createElement('span');
            div.id   = 'min_' + boxId;
            onMouseClick = miolo.associateObjWithEvent(this,'showBox');
			Event.observe(div,'click',onMouseClick);
            var text;
			var ele = document.getElementsByClassName('caption',box);
            if ( ele.length > 0 )
            {
               text = ele[0].innerHTML;
            }
            else
            {
               text = 'Box';
            }
            div.innerHTML = text;
            div.className = 'm-box-title-minimized';

            cont.appendChild(div);
            box.style.display = 'none';
            cont.style.textAlign = 'left';
        }
        else
        {
            div.style.display = div.style.display == 'none' ? '' : 'none';
            box.style.display = 'none';
        }
    },
    hideBoxContent: function ( box ) {
        //hide all box contents
        for ( var i = miolo.isIE ? 1 : 2; i< box.childNodes.length; i++ )
        {
            style = box.childNodes[i].style;
            if( typeof(style) != 'undefined') style.display = style.display == 'none' ? '' : 'none';
        }
    },
    moveBox: function( e, box, move ) {
        if( box.style.display == 'none' )
            return false;
        if ( ! Event.isLeftClick(e) ) { //case the mouse button is not the left
            return this.closeBox( e, box );
        }
        //control the box click and drag
		if (move) {
            Event.observe(document,'mousemove',this.onMouseMoveHandler);
		}
		else {
    		Event.stopObserving(document,'mousemove',this.onMouseMoveHandler);
		}
        this.boxToMove = box;
        this.boxToMove.style.position = 'relative';

        if( move )  //if click, control the initial positions
        {
            var diffLeft = this.boxToMove.style.left ? parseInt(this.boxToMove.style.left) : 0;
            var diffTop  = this.boxToMove.style.top  ? parseInt(this.boxToMove.style.top ) : 0;
            this.boxPositions[0] = Event.pointerX(e) - diffLeft;
            this.boxPositions[1] = Event.pointerY(e) - diffTop;
        }
        this.boxMoving = move; //control if is to move the box

        if ( ! move )
        {
            this.boxToMove.style.position = 'absolute';
            document.cookie = this.boxToMove.id + '_position=' + this.boxToMove.style.left + ',' +
                          this.boxToMove.style.top         + ',' + this.boxToMove.tagName + ',' +
                          this.boxToMove.className;
            this.boxToMove.style.position = 'relative';
        }
        return ! move; //if move = false, disable text selection else enable
    },
    boxPosition: function ( event, element ) {
        var posX = Event.pointerX(event); //control the top left
        var posY = Event.pointerY(event);; //control the top position
        var st = this.boxToMove.style; //the box style
        st.left = (posX - this.boxPositions[0] ) + "px"; //set the left position
        st.top  = (posY - this.boxPositions[1] ) + "px"; //set the top  position
    },
    setBoxPositions: function( ) {

        var cookies = document.cookie.split(';');

        for( var i=0; i < cookies.length; i++ )
        {
            var pos = cookies[i].indexOf('_position');
            if( pos > 0 )
            {
                var id  = cookies[i].substr( 1, pos-1);
                var box = miolo.getElementById( id );
                var aux = cookies[i].split('=')[1].split(',');

                if( box != null && box.tagName == aux[2] && box.className == aux[3] )
                {
                    box.style.position = 'absolute';
                    box.style.left     = aux[0];
                    box.style.top      = aux[1];
                    box.style.position = 'relative';
                }
            }
        }
    }
});

miolo.box = new Miolo.Box();