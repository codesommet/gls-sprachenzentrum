/**
 * GLS WhatsApp Support Section
 * File: public/assets/js/bookonwhatsapp.js
 *
 * Handles: tab chips, mouse glow, micro bubbles, phone tilt,
 *          animated chat conversation with page-clear, end badge & loop.
 *
 * Reads chat steps from data-chat-center / data-chat-online JSON (Blade-injected).
 * Reads centerName from data-center-name attribute on [data-gls-wa].
 * Reads waType from data-wa-type attribute ('center' | 'online').
 */
(function(){
  'use strict';

  var section = document.querySelector('[data-gls-wa]');
  if(!section) return;

  var phone = section.querySelector('.gls-wa-phone');
  var glow  = section.querySelector('.gls-mouse-glow');
  var bubbleLayer = section.querySelector('.gls-mouse-bubbles');
  var chat = section.querySelector('[data-chat]');
  var timeEl = section.querySelector('[data-time]');

  // Read attributes from DOM
  var centerName = section.getAttribute('data-center-name') || 'GLS Sprachenzentrum';
  var waType     = section.getAttribute('data-wa-type') || 'center';
  var endLabel   = section.getAttribute('data-end-label') || 'Conversation terminée ✅';

  // Parse chat steps from data attributes (Blade-injected JSON)
  var centerSteps = [];
  var onlineSteps = [];
  try { centerSteps = JSON.parse(section.getAttribute('data-chat-center') || '[]'); } catch(e){}
  try { onlineSteps = JSON.parse(section.getAttribute('data-chat-online') || '[]'); } catch(e){}

  // Inject :center placeholder replacement
  function replacePlaceholders(steps){
    return steps.map(function(s){
      var copy = {};
      for(var k in s) copy[k] = s[k];
      if(copy.html) copy.html = copy.html.replace(/:center/g, centerName);
      return copy;
    });
  }
  centerSteps = replacePlaceholders(centerSteps);
  onlineSteps = replacePlaceholders(onlineSteps);

  // Select conversation based on page type
  var steps = waType === 'online' ? onlineSteps : centerSteps;

  // =============================
  // Tabs active (UI only)
  // =============================
  var chips = section.querySelectorAll('.gls-wa-chip');
  chips.forEach(function(chip){
    chip.addEventListener('click', function(){
      chips.forEach(function(c){ c.classList.remove('is-active'); });
      chip.classList.add('is-active');
    });
  });

  // =============================
  // Mouse effect: glow + micro bubbles + phone tilt
  // =============================
  var lastBubbleAt = 0;

  function spawnMouseBubble(x, y){
    var now = Date.now();
    if(now - lastBubbleAt < 35) return;
    lastBubbleAt = now;

    var b = document.createElement('span');
    b.className = 'gls-mbubble';
    b.style.left = x + 'px';
    b.style.top  = y + 'px';
    b.style.width = (4 + Math.random()*6) + 'px';
    b.style.height = b.style.width;
    bubbleLayer.appendChild(b);
    setTimeout(function(){ b.remove(); }, 1400);
  }

  function onMove(e){
    var r = section.getBoundingClientRect();
    var x = e.clientX - r.left;
    var y = e.clientY - r.top;

    var px = x / r.width;
    var py = y / r.height;
    var bx = (px - 0.5) * 18;
    var by = (py - 0.5) * 18;
    section.style.backgroundPosition = (50 + bx) + '% ' + (50 + by) + '%';

    if(glow){
      glow.style.left = x + 'px';
      glow.style.top  = y + 'px';
      glow.style.opacity = 1;
    }

    if(phone){
      var rx = (py - 0.5) * -6;
      var ry = (px - 0.5) *  8;
      phone.style.transform = 'perspective(900px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg) translateZ(0)';
    }

    if(bubbleLayer){
      spawnMouseBubble(x + (Math.random()*14 - 7), y + (Math.random()*14 - 7));
    }
  }

  function onLeave(){
    section.style.backgroundPosition = '50% 50%';
    if(glow) glow.style.opacity = 0;
    if(phone) phone.style.transform = 'none';
  }

  section.addEventListener('mousemove', onMove);
  section.addEventListener('mouseleave', onLeave);

  // =============================
  // Chat conversation animation
  // =============================
  function setTime(hhmm){
    if(timeEl) timeEl.textContent = hhmm;
  }

  function makeBubble(side, html, isGreen){
    var div = document.createElement('div');
    div.className = 'gls-wa-bubble gls-' + side + (isGreen ? ' gls-green' : '');
    div.innerHTML = html;
    return div;
  }

  function makeTyping(side){
    var t = document.createElement('div');
    t.className = 'gls-typing gls-' + side;
    t.innerHTML = '<span></span><span></span><span></span>';
    return t;
  }

  // Soft page-clear: fade out → empty → fade in
  function clearChatSoft(callback){
    if(!chat) { if(callback) callback(); return; }
    chat.style.opacity = '0';
    setTimeout(function(){
      chat.innerHTML = '';
      chat.style.opacity = '1';
      if(callback) callback();
    }, 480);
  }

  // Final confirmation message (dynamic per page type)
  function showEndBadge(callback){
    if(!chat) { if(callback) callback(); return; }

    var endHtml;
    if(waType === 'online'){
      endHtml = '\u{1F4C5} Votre classe commence le 15 mars \u00E0 18:00<br>Je vous envoie le lien Zoom dans quelques instants.';
    } else {
      endHtml = '\u{1F4C5} Votre classe commence le 15 mars \u00E0 09:00<br>Nous vous attendons au centre GLS.';
    }

    // Show typing indicator first, then replace with agent bubble
    var typing = makeTyping('left');
    chat.appendChild(typing);

    setTimeout(function(){
      typing.remove();
      var bubble = makeBubble('left', endHtml, false);
      chat.appendChild(bubble);
      if(callback) setTimeout(callback, 500);
    }, 1200);
  }

  // =============================
  // Run conversation with loop
  // =============================
  function runConversation(){
    if(!chat || !steps.length) return;
    chat.innerHTML = '';
    chat.style.opacity = '1';

    var i = 0;
    var pendingClear = false;

    function nextStep(){
      if(i >= steps.length){
        // End: show badge, wait 5s, clear and restart
        showEndBadge(function(){
          setTimeout(function(){
            clearChatSoft(function(){
              runConversation();
            });
          }, 5000);
        });
        return;
      }

      var s = steps[i];
      i++;

      // If the previous step had clear=true, soft-clear before continuing
      if(pendingClear){
        pendingClear = false;
        clearChatSoft(function(){
          renderStep(s);
        });
        return;
      }

      renderStep(s);
    }

    function renderStep(s){
      setTime(s.time);

      // Check if THIS step triggers a clear AFTER it's shown
      var willClear = !!s.clear;

      // Typing duration: base 900ms, slightly longer for left (agent)
      var typingMs = s.side === 'left' ? 1100 : 900;
      // Vary a bit by text length
      if(s.html && s.html.length > 80) typingMs += 200;

      var typing = makeTyping(s.side);
      chat.appendChild(typing);

      setTimeout(function(){
        typing.remove();
        var bubble = makeBubble(s.side, s.html, !!s.green);
        chat.appendChild(bubble);

        if(willClear) pendingClear = true;

        setTimeout(nextStep, 500);
      }, typingMs);
    }

    nextStep();
  }

  // Start when visible (IntersectionObserver)
  var started = false;
  if('IntersectionObserver' in window){
    var io = new IntersectionObserver(function(entries){
      if(started) return;
      for(var j = 0; j < entries.length; j++){
        if(entries[j].isIntersecting){
          started = true;
          runConversation();
          break;
        }
      }
    }, { threshold: 0.25 });
    io.observe(section);
  } else {
    runConversation();
  }

})();
