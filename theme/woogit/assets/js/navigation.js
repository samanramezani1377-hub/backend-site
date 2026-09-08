(function(){'use strict';document.addEventListener('DOMContentLoaded',function(){var b=document.querySelector('.wg-menu-toggle'),n=document.getElementById('wg-nav');if(!b||!n)return;var previousFocus=null;
function isOpen(){return b.getAttribute('aria-expanded')==='true';}
function focusables(){return Array.prototype.slice.call(n.querySelectorAll(window.WooGit&&WooGit.focusable||'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled])'));}
function close(restore){b.setAttribute('aria-expanded','false');n.classList.remove('is-open');n.setAttribute('aria-hidden','true');if(restore&&previousFocus)previousFocus.focus();previousFocus=null;}
function open(){previousFocus=document.activeElement;b.setAttribute('aria-expanded','true');n.classList.add('is-open');n.setAttribute('aria-hidden','false');var first=focusables()[0];if(first)first.focus();}
b.addEventListener('click',function(){isOpen()?close(true):open();});
document.addEventListener('click',function(e){if(isOpen()&&!n.contains(e.target)&&e.target!==b)close(true);});
document.addEventListener('keydown',function(e){if(!isOpen())return;if(e.key==='Escape'){e.preventDefault();close(true);return;}if(e.key!=='Tab')return;var items=focusables();if(!items.length)return;var first=items[0],last=items[items.length-1];if(e.shiftKey&&document.activeElement===first){e.preventDefault();last.focus();}else if(!e.shiftKey&&document.activeElement===last){e.preventDefault();first.focus();}});
window.addEventListener('resize',function(){if(window.innerWidth>900&&isOpen())close(false);});close(false);
});})();