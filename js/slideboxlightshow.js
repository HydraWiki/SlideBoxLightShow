mw.sbls = {
	slideIds: [],
	slideIndexes: [],

	/**
	 * Starts up slide box light shows for each available container.
	 *
	 * @param	object	Container object selected from a class or ID.
	 * @return	void	[Outputs to Screen]
	 */
	doSlideBoxLightShow: function(container) {
		//Grab data.
		var sequence = $(container).attr('data-sequence');
		var interval = $(container).attr('data-interval');
		var containerId = $(container).attr('id');

		this.slideIds[containerId] = [];
		this.slideIndexes[containerId] = 0;

		//Grab slide IDs
		$('#'+containerId+' .sbls-image').each(function() {
		    mw.sbls.slideIds[containerId].push($(this).attr('id'));
		});

		//Change sequence as needed.
		if (sequence == 'reverse') {
			this.slideIds[containerId].reverse();
		} else if (sequence == 'random') {
			this.slideIds[containerId] = this.shuffle(this.slideIds[containerId]);
		}

		//Hide all slides and set z-index.
		var i = this.slideIds[containerId].length;
		this.slideIds[containerId].forEach(function(element) {
			$('#'+element).css({'z-index': i});
			$('#'+element).hide();
			i--;
		});
		//Show the first one in the sequence.
		$('#'+this.slideIds[containerId][0]).show();

		if (this.slideIds[containerId].length > 1) {
			setInterval(function() {
				mw.sbls.rotateSlides(container, 'forward')
			}, interval);
		}

		console.log(this.slideIds[containerId]);
	},

	/**
	 * Rotates slides inside the provided container.
	 *
	 * @param	string	Container of slides to work through.
	 * @return	void
	 */
	rotateSlides: function(container, direction) {
		if (!direction) {
			direction = 'forward';
		}
		var transition = $(container).attr('data-transition');
		var transitionspeed = parseInt($(container).attr('data-transitionspeed'));

		var containerId = $(container).attr('id');
		var slideMaxIndex = this.slideIds[containerId].length - 1;
		var previousSlide = this.slideIds[containerId][this.slideIndexes[containerId]];

		if (direction == 'forward') {
			if (this.slideIndexes[containerId] == slideMaxIndex) {
				var nextSlide = this.slideIds[containerId][0];
				this.slideIndexes[containerId] = 0;
			} else {
				var nextSlide = this.slideIds[containerId][this.slideIndexes[containerId] + 1];
				this.slideIndexes[containerId] = this.slideIndexes[containerId] + 1;
			}
		} else {
			if (this.slideIndexes[containerId] == 0) {
				var nextSlide = this.slideIds[containerId][slideMaxIndex];
				this.slideIndexes[containerId] = slideMaxIndex;
			} else {
				var nextSlide = this.slideIds[containerId][this.slideIndexes[containerId] - 1];
				this.slideIndexes[containerId] = this.slideIndexes[containerId] - 1;
			}
		}

		if (!transition || transition == 'fade') {
			$('#'+nextSlide).fadeIn(transitionspeed);
			$('#'+previousSlide).fadeOut(transitionspeed);
		} else if (transition == 'up') {
			var previousTop = $('#'+previousSlide).css('top');
			$('#'+nextSlide).css({top: '100%'});
			$('#'+nextSlide).show();
			$('#'+nextSlide).animate({top: previousTop}, transitionspeed, function() {
				$('#'+nextSlide).css({top: previousTop});
			});
			$('#'+previousSlide).animate({top: '-100%'}, transitionspeed, function() {
				$('#'+previousSlide).hide();
				$('#'+previousSlide).css({top: previousTop});
			});
		} else if (transition == 'down') {
			var previousTop = $('#'+previousSlide).css('top');
			$('#'+nextSlide).css({top: '-100%'});
			$('#'+nextSlide).show();
			$('#'+nextSlide).animate({top: previousTop}, transitionspeed, function() {
				$('#'+nextSlide).css({top: previousTop});
			});
			$('#'+previousSlide).animate({top: '100%'}, transitionspeed, function() {
				$('#'+previousSlide).hide();
				$('#'+previousSlide).css({top: previousTop});
			});
		} else if (transition == 'left') {
			var previousLeft = $('#'+previousSlide).css('left');
			$('#'+nextSlide).css({left: '100%'});
			$('#'+nextSlide).show();
			$('#'+nextSlide).animate({left: previousLeft}, transitionspeed, function() {
				$('#'+nextSlide).css({left: previousLeft});
			});
			$('#'+previousSlide).animate({left: '-100%'}, transitionspeed, function() {
				$('#'+previousSlide).hide();
				$('#'+previousSlide).css({left: previousLeft});
			});
		} else if (transition == 'right') {
			var previousLeft = $('#'+previousSlide).css('left');
			$('#'+nextSlide).css({left: '-100%'});
			$('#'+nextSlide).show();
			$('#'+nextSlide).animate({left: previousLeft}, transitionspeed, function() {
				$('#'+nextSlide).css({left: previousLeft});
			});
			$('#'+previousSlide).animate({left: '100%'}, transitionspeed, function() {
				$('#'+previousSlide).hide();
				$('#'+previousSlide).css({left: previousLeft});
			});
		}
	},

	/**
	 * Random Shuffler
	 * https://github.com/coolaj86/knuth-shuffle
	 *
	 * @param	array	Array to randomize.
	 * @return	array	Randomized array.
	 */
	shuffle: function(array) {
		var currentIndex = array.length
			, temporaryValue
			, randomIndex
			;

		// While there remain elements to shuffle...
		while (0 !== currentIndex) {
			// Pick a remaining element...
			randomIndex = Math.floor(Math.random() * currentIndex);
			currentIndex -= 1;

			// And swap it with the current element.
			temporaryValue = array[currentIndex];
			array[currentIndex] = array[randomIndex];
			array[randomIndex] = temporaryValue;
		}

		return array;
	}
}

$(document).ready(function() {
	$('.slideboxlightshow').each(function() {
		mw.sbls.doSlideBoxLightShow($(this));
	});

	$('.sbls-prev').click(function() {
		var container = $(this).parent().prev('.slideboxlightshow');
		mw.sbls.rotateSlides(container, 'reverse');
	});

	$('.sbls-next').click(function() {
		var container = $(this).parent().prev('.slideboxlightshow');
		mw.sbls.rotateSlides(container, 'forward');
	});
});