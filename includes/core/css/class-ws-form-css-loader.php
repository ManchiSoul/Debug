<?php

	// CSS spinners adapted from https://github.com/vineethtrv/css-loader (Copyright (c) 2020 Vineeth.TR - MIT License)

	class WS_Form_CSS_Loader extends WS_Form_CSS {

		// Render
		public function render_loader() {
?>
:root {
	--wsf-loader-fade-in-duration: 0s;
	--wsf-loader-fade-out-duration: 0s;
	--wsf-loader-overlay-color: 255, 255, 255;
	--wsf-loader-overlay-opacity: 0.5;
	--wsf-loader-overlay-cursor: wait;
	--wsf-loader-overlay-z-index: 2;
	--wsf-loader-sprite-animation-duration: 1s;
	--wsf-loader-sprite-border: 5px;
	--wsf-loader-sprite-color: 0, 0, 0;
	--wsf-loader-sprite-color-accent: 255, 61, 0;
	--wsf-loader-sprite-offset-top: -10px;
	--wsf-loader-sprite-offset-top-align: 0;
	--wsf-loader-sprite-offset-top-always-visible: 0;
	--wsf-loader-sprite-offset-left: 0;
	--wsf-loader-sprite-offset-left-align: 0;
	--wsf-loader-sprite-opacity: 1;
	--wsf-loader-sprite-opacity-accent: 1;
	--wsf-loader-sprite-size: 48px;
	--wsf-loader-text-display: none;
	--wsf-loader-text-margin-top: 10px;
}

.wsf-loader {
	background: rgba(var(--wsf-loader-overlay-color), var(--wsf-loader-overlay-opacity));
	cursor: var(--wsf-loader-overlay-cursor);
	display: none;
	margin: 0;
	padding: 0;
	position: absolute;
	user-select: none;
	z-index: var(--wsf-loader-overlay-z-index);
}

.wsf-form-loader-show .wsf-loader {
	animation: wsf-fade-in var(--wsf-loader-fade-in-duration);
}

.wsf-form-loader-hide .wsf-loader {
	animation: wsf-fade-out var(--wsf-loader-fade-out-duration);
}

.wsf-loader-inner {
	box-sizing: border-box;
	display: inline-block;
	left: calc(50% + var(--wsf-loader-sprite-offset-left) + var(--wsf-loader-sprite-offset-left-align));
	margin: 0;
	padding: 0;
	position: relative;
	text-align: center;
	top: calc(50% + var(--wsf-loader-sprite-offset-top) + var(--wsf-loader-sprite-offset-top-align) + var(--wsf-loader-sprite-offset-top-always-visible));
}

.wsf-loader-inner .wsf-loader-sprite {
	border-radius: 50%;
	color: var(--wsf-loader-sprite-color);
	display: inline-block;
	position: relative;
	text-indent: -9999em;
	transform: translateZ(0);
	user-select: none;
}

.wsf-loader-inner p {
	display: var(--wsf-loader-text-display);
	margin: 0;
	margin-top: var(--wsf-loader-text-margin-top);
	padding: 0;
	user-select: none;
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-rotate-25-gap {
	animation: wsf-loader-animation-rotate var(--wsf-loader-sprite-animation-duration) infinite linear;
	border: var(--wsf-loader-sprite-border) solid rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	border-bottom-color: transparent;
	height: var(--wsf-loader-sprite-size);
	width: var(--wsf-loader-sprite-size);
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-rotate-25-accent {
	animation: wsf-loader-animation-rotate var(--wsf-loader-sprite-animation-duration) infinite linear;
	border: var(--wsf-loader-sprite-border) solid rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	border-bottom-color: rgba(var(--wsf-loader-sprite-color-accent), var(--wsf-loader-sprite-opacity-accent));
	height: var(--wsf-loader-sprite-size);
	width: var(--wsf-loader-sprite-size);
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-rotate-grow {
	animation: wsf-loader-animation-rotate var(--wsf-loader-sprite-animation-duration) infinite linear;
	height: var(--wsf-loader-sprite-size);
	width: var(--wsf-loader-sprite-size);
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-rotate-grow:before {
	animation: wsf-loader-animation-rotate-grow 2s linear infinite ;
	border-radius: 50%;
	border: var(--wsf-loader-sprite-border) solid rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	content: "";
	inset: 0;
	position: absolute;
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-circle-dots-pulse {
	animation: wsf-loader-animation-circle-dots-pulse var(--wsf-loader-sprite-animation-duration) infinite linear;
	font-size: calc(var(--wsf-loader-sprite-size) / 7);
	height: 1em;
	width: 1em;
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-rotate-dots {
	animation: wsf-loader-animation-rotate var(--wsf-loader-sprite-animation-duration) infinite linear;
	border: var(--wsf-loader-sprite-border) dotted rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	height: var(--wsf-loader-sprite-size);
	width: var(--wsf-loader-sprite-size);
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-circle-dots {
	animation: wsf-loader-animation-circle-dots var(--wsf-loader-sprite-animation-duration) infinite ease;
	font-size: calc(var(--wsf-loader-sprite-size) / 6);
	height: 1em;
	width: 1em;
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-rotate-dots-tail {
	animation: wsf-loader-animation-rotate var(--wsf-loader-sprite-animation-duration) infinite linear;
	display: inline-block;
	border-top: var(--wsf-loader-sprite-border) dotted rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	border-right: var(--wsf-loader-sprite-border) dotted transparent;
	height: var(--wsf-loader-sprite-size);
	width: var(--wsf-loader-sprite-size);
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-rotate-dots-pulse {
	animation: wsf-loader-animation-rotate-dots-pulse var(--wsf-loader-sprite-animation-duration) infinite ease, wsf-loader-animation-rotate var(--wsf-loader-sprite-animation-duration) infinite ease;
	font-size: calc(var(--wsf-loader-sprite-size) / 1.8);
	height: 1em;
	overflow: hidden;
	width: 1em;
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-horizontal-dots-pulse,
.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-horizontal-dots-pulse:before,
.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-horizontal-dots-pulse:after {
	animation-fill-mode: both;
	animation: wsf-loader-animation-horizontal-dots-pulse var(--wsf-loader-sprite-animation-duration) infinite ease-in-out;
	height: 2.5em;
	width: 2.5em;
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-horizontal-dots-pulse {
	animation-delay: -0.16s;
	font-size: calc(var(--wsf-loader-sprite-size) / 9.5);
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-horizontal-dots-pulse:before,
.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-horizontal-dots-pulse:after {
	border-radius: 50%;
	content: '';
	position: absolute;
	top: 0;
}
.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-horizontal-dots-pulse:before {
	animation-delay: -0.32s;
	left: -3.5em;
}
.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-horizontal-dots-pulse:after {
	left: 3.5em;
}

.wsf-loader-inner .wsf-loader-sprite.wsf-loader-sprite-horizontal-dots-accent {
	animation: wsf-loader-animation-horizontal-dots-accent var(--wsf-loader-sprite-animation-duration) infinite linear;
	background: rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	box-shadow: calc(var(--wsf-loader-sprite-size) / 2.5 * -1) 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), calc(var(--wsf-loader-sprite-size) / 2.5) 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	height: calc(var(--wsf-loader-sprite-size) / 5);
	width: calc(var(--wsf-loader-sprite-size) / 5);
}

@keyframes wsf-fade-in {
	0% { opacity: 0; }
	100% { opacity: 1; }	
}

@keyframes wsf-fade-out {
	0% { opacity: 1; }
	100% { opacity: 0; }	
}

@keyframes wsf-loader-animation-rotate {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
} 

@keyframes wsf-loader-animation-rotate-grow {
	0%   {clip-path:polygon(50% 50%,0 0,0 0,0 0,0 0,0 0)}
	25%  {clip-path:polygon(50% 50%,0 0,100% 0,100% 0,100% 0,100% 0)}
	50%  {clip-path:polygon(50% 50%,0 0,100% 0,100% 100%,100% 100%,100% 100%)}
	75%  {clip-path:polygon(50% 50%,0 0,100% 0,100% 100%,0 100%,0 100%)}
	100% {clip-path:polygon(50% 50%,0 0,100% 0,100% 100%,0 100%,0 0)}
}

@keyframes wsf-loader-animation-circle-dots-pulse {
	0%,
	100% {
		box-shadow: 0 -3em 0 0.2em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em -2em 0 0em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 3em 0 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em 2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 0 3em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em 2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -3em 0 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em -2em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	}
	12.5% {
		box-shadow: 0 -3em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em -2em 0 0.2em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 3em 0 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em 2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 0 3em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em 2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -3em 0 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em -2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	}
	25% {
		box-shadow: 0 -3em 0 -0.5em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em -2em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 3em 0 0 0.2em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em 2em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 0 3em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em 2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -3em 0 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em -2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	}
	37.5% {
		box-shadow: 0 -3em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em -2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)),
		3em 0em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em 2em 0 0.2em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 0 3em 0 0em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em 2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -3em 0em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em -2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	}
	50% {
		box-shadow: 0 -3em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em -2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)),
		3em 0 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em 2em 0 0em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 0 3em 0 0.2em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em 2em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -3em 0em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em -2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	}
	62.5% {
		box-shadow: 0 -3em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em -2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)),
		3em 0 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em 2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 0 3em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em 2em 0 0.2em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -3em 0 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em -2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	}
	75% {
		box-shadow: 0em -3em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em -2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 3em 0em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em 2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 0 3em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em 2em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -3em 0em 0 0.2em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em -2em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	}
	87.5% {
		box-shadow: 0em -3em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em -2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 3em 0 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 2em 2em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), 0 3em 0 -1em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em 2em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -3em 0em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), -2em -2em 0 0.2em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	}
}

@keyframes wsf-loader-animation-circle-dots {
	0%,
	100% {
		box-shadow: 0em -2.6em 0em 0em rgba(var(--wsf-loader-sprite-color), 1), 1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 2.5em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 1.75em 1.75em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 0em 2.5em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -1.8em 1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -2.6em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.5), -1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.7);
	}
	12.5% {
		box-shadow: 0em -2.6em 0em 0em rgba(var(--wsf-loader-sprite-color), 0.7), 1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 1), 2.5em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 1.75em 1.75em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 0em 2.5em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -1.8em 1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -2.6em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.5);
	}
	25% {
		box-shadow: 0em -2.6em 0em 0em rgba(var(--wsf-loader-sprite-color), 0.5), 1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.7), 2.5em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 1), 1.75em 1.75em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 0em 2.5em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -1.8em 1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -2.6em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2);
	}
	37.5% {
		box-shadow: 0em -2.6em 0em 0em rgba(var(--wsf-loader-sprite-color), 0.2), 1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.5), 2.5em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.7), 1.75em 1.75em 0 0em rgba(var(--wsf-loader-sprite-color), 1), 0em 2.5em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -1.8em 1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -2.6em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2);
	}
	50% {
		box-shadow: 0em -2.6em 0em 0em rgba(var(--wsf-loader-sprite-color), 0.2), 1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 2.5em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.5), 1.75em 1.75em 0 0em rgba(var(--wsf-loader-sprite-color), 0.7), 0em 2.5em 0 0em rgba(var(--wsf-loader-sprite-color), 1), -1.8em 1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -2.6em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2);
	}
	62.5% {
		box-shadow: 0em -2.6em 0em 0em rgba(var(--wsf-loader-sprite-color), 0.2), 1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 2.5em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 1.75em 1.75em 0 0em rgba(var(--wsf-loader-sprite-color), 0.5), 0em 2.5em 0 0em rgba(var(--wsf-loader-sprite-color), 0.7), -1.8em 1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 1), -2.6em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2);
	}
	75% {
		box-shadow: 0em -2.6em 0em 0em rgba(var(--wsf-loader-sprite-color), 0.2), 1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 2.5em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 1.75em 1.75em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 0em 2.5em 0 0em rgba(var(--wsf-loader-sprite-color), 0.5), -1.8em 1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.7), -2.6em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 1), -1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2);
	}
	87.5% {
		box-shadow: 0em -2.6em 0em 0em rgba(var(--wsf-loader-sprite-color), 0.2), 1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 2.5em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 1.75em 1.75em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), 0em 2.5em 0 0em rgba(var(--wsf-loader-sprite-color), 0.2), -1.8em 1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 0.5), -2.6em 0em 0 0em rgba(var(--wsf-loader-sprite-color), 0.7), -1.8em -1.8em 0 0em rgba(var(--wsf-loader-sprite-color), 1);
	}
}

@keyframes wsf-loader-animation-rotate-dots-pulse {
	0% {
		box-shadow: 0 -0.83em 0 -0.4em, 0 -0.83em 0 -0.42em, 0 -0.83em 0 -0.44em, 0 -0.83em 0 -0.46em, 0 -0.83em 0 -0.477em;
	}
	5%,
	95% {
		box-shadow: 0 -0.83em 0 -0.4em, 0 -0.83em 0 -0.42em, 0 -0.83em 0 -0.44em, 0 -0.83em 0 -0.46em, 0 -0.83em 0 -0.477em;
	}
	10%,
	59% {
		box-shadow: 0 -0.83em 0 -0.4em, -0.087em -0.825em 0 -0.42em, -0.173em -0.812em 0 -0.44em, -0.256em -0.789em 0 -0.46em, -0.297em -0.775em 0 -0.477em;
	}
	20% {
		box-shadow: 0 -0.83em 0 -0.4em, -0.338em -0.758em 0 -0.42em, -0.555em -0.617em 0 -0.44em, -0.671em -0.488em 0 -0.46em, -0.749em -0.34em 0 -0.477em;
	}
	38% {
		box-shadow: 0 -0.83em 0 -0.4em, -0.377em -0.74em 0 -0.42em, -0.645em -0.522em 0 -0.44em, -0.775em -0.297em 0 -0.46em, -0.82em -0.09em 0 -0.477em;
	}
	100% {
		box-shadow: 0 -0.83em 0 -0.4em, 0 -0.83em 0 -0.42em, 0 -0.83em 0 -0.44em, 0 -0.83em 0 -0.46em, 0 -0.83em 0 -0.477em;
	}
}

@keyframes wsf-loader-animation-horizontal-dots-pulse {
	0%, 80%, 100% { box-shadow: 0 2.5em 0 -1.3em rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)) }
	40% { box-shadow: 0 2.5em 0 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)) }
}

@keyframes wsf-loader-animation-horizontal-dots-accent {
	25% {
		background: rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
		box-shadow: calc(var(--wsf-loader-sprite-size) / 2.5 * -1) 0 rgba(var(--wsf-loader-sprite-color-accent), var(--wsf-loader-sprite-opacity-accent)), calc(var(--wsf-loader-sprite-size) / 2.5) 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	}
	50% {
		background: rgba(var(--wsf-loader-sprite-color-accent), var(--wsf-loader-sprite-opacity-accent));
		box-shadow: calc(var(--wsf-loader-sprite-size) / 2.5 * -1) 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), calc(var(--wsf-loader-sprite-size) / 2.5) 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
	}
	75% {
		background: rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity));
		box-shadow: calc(var(--wsf-loader-sprite-size) / 2.5 * -1) 0 rgba(var(--wsf-loader-sprite-color), var(--wsf-loader-sprite-opacity)), calc(var(--wsf-loader-sprite-size) / 2.5) 0 rgba(var(--wsf-loader-sprite-color-accent), var(--wsf-loader-sprite-opacity-accent));
	}
}
<?php
		}

		// Skin - RTL
		public function render_loader_rtl() {}
	}
