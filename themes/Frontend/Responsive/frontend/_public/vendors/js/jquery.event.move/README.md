#jquery.event.move

Move events provide an easy way to set up press-move-release interactions on
mouse and touch devices.

*UPDATE 2.0*: `move` events are now compatible with jQuery 3.x. In addition, the
underlying implementation is rewritten using vanilla DOM (where before it
was jQuery special events only) – jQuery is no longer a requirement. However,
if you do not have jQuery you will require a polyfill for Object.assign to
support older browsers. I can recommend <a href="https://github.com/cruncher/object.assign">Object.assign polyfill</a> :)

##Demo and docs

<a href="http://stephband.info/jquery.event.move/">stephband.info/jquery.event.move/</a>

##Move events

<dl>
	<dt>movestart</dt>
	<dd>Fired following mousedown or touchstart, when the pointer crosses a threshold distance from the position of the mousedown or touchstart.</dd>
	
	<dt>move</dt>
	<dd>Fired on every animation frame where a mousemove or touchmove has changed the cursor position.</dd>
	
	<dt>moveend</dt>
	<dd>Fired following mouseup or touchend, after the last move event, and in the case of touch events when the finger that started the move has been lifted.</dd>
</dl>

Move event objects are augmented with the properties:

<dl>
  <dt>e.pageX<br/>e.pageY</dt>
  <dd>Current page coordinates of pointer.</dd>
  
  <dt>e.startX<br/>e.startY</dt>
  <dd>Page coordinates the pointer had at movestart.</dd>
  
  <dt>e.deltaX<br/>e.deltaY</dt>
  <dd>Distance the pointer has moved since movestart.</dd>

  <dt>e.velocityX<br/>e.velocityY</dt>
  <dd>Velocity in pixels/ms, averaged over the last few events.</dd>
</dl>

## Usage

Use them in the same way as you bind to any other DOM event:

    var node = document.querySelector('.mydiv');
    
    // A movestart event must be bound and explicitly
    // enabled or other move events will not fire
    node.addEventListener('movestart', function(e) {
      e.enableMove();
    });
    
    node.addEventListener('move', function(e) {
      // move .mydiv horizontally
      this.style.left = (e.startX + e.distX) + 'px';
    });
    
    node.addEventListener('moveend', function() {
      // move is complete!
    });

Or if you have jQuery in your project:

    jQuery('.mydiv')
    .on('move', function(e) {
      // move .mydiv horizontally
      jQuery(this).css({ left: e.startX + e.deltaX });
    
    }).bind('moveend', function() {
      // move is complete!
    });

(`.enableMove()` is a performance optimisation that avoids unnecessarily
sending `move` when there are no listeners. jQuery's special event system
does the work of enabling move events so using jQuery there is no need to
explicitly bind to `movestart`.)

To see an example of what could be done with it, <a href="http://stephband.info/jquery.event.move/">stephband.info/jquery.event.move/</a>

##CommonJS

If you're using Browserify, or any other CommonJS-compatible module system,
you can require this script by passing it your jQuery reference. For example,

<pre><code class="js">
require('./path/to/jquery.event.move.js')();
</code></pre>

##Tweet me

If you use move events on something interesting, tweet me <a href="http://twitter.com/stephband">@stephband</a>!
