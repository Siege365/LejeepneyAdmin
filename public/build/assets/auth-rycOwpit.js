document.addEventListener("DOMContentLoaded",function(){h(),g(),w(),v(),b(),L(),E()});function h(){const t=document.getElementById("loginForm");t&&l(t);const e=document.getElementById("signupForm");e&&l(e);const s=document.getElementById("forgotForm");s&&l(s),document.querySelectorAll(".auth-form").forEach(n=>{n.id||l(n)})}function l(t){const e=t.querySelectorAll('input[required], input[type="email"], input[type="password"]');e.forEach(s=>{s.addEventListener("blur",function(){u(this)}),s.addEventListener("input",function(){this.closest(".input-wrapper")?.classList.contains("error")&&u(this)})}),t.addEventListener("submit",function(s){let n=!0;e.forEach(r=>{u(r)||(n=!1)});const o=t.querySelector('input[name="password"]'),i=t.querySelector('input[name="password_confirmation"]');if(o&&i&&o.value!==i.value&&(c(i,"Passwords do not match"),n=!1),n){const r=t.querySelector('button[type="submit"]');r&&(r.disabled=!0,r.classList.add("loading"),r.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Please wait...')}else{s.preventDefault();const r=t.closest(".auth-card");r&&(r.classList.add("shake"),setTimeout(()=>r.classList.remove("shake"),500));const a=t.querySelector(".input-wrapper.error input");a&&a.focus(),d("Please fix the errors before continuing","error")}})}function u(t){const e=t.value.trim(),s=t.type,n=t.name;return t.required&&!e?(c(t,"This field is required"),!1):s==="email"&&e&&!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e)?(c(t,"Please enter a valid email address"),!1):s==="password"&&e&&n==="password"&&e.length<8?(c(t,"Password must be at least 8 characters"),!1):n==="name"&&e&&e.length<2?(c(t,"Name must be at least 2 characters"),!1):(e&&y(t),!0)}function c(t,e){const s=t.closest(".input-wrapper"),o=t.closest(".form-group")?.querySelector(".error-message");s&&(s.classList.remove("success"),s.classList.add("error")),o&&(o.textContent=e,o.classList.add("show"),o.style.animation="slideDown 0.2s ease forwards")}function y(t){const e=t.closest(".input-wrapper"),n=t.closest(".form-group")?.querySelector(".error-message");e&&(e.classList.remove("error"),e.classList.add("success")),n&&(n.textContent="",n.classList.remove("show"))}function g(){document.querySelectorAll(".toggle-password").forEach(e=>{e.addEventListener("click",function(){const n=this.closest(".input-wrapper")?.querySelector("input"),o=this.querySelector("i");!n||!o||(n.type==="password"?(n.type="text",o.classList.remove("fa-eye"),o.classList.add("fa-eye-slash"),this.setAttribute("title","Hide password")):(n.type="password",o.classList.remove("fa-eye-slash"),o.classList.add("fa-eye"),this.setAttribute("title","Show password")),n.focus(),this.style.transform="translateY(-50%) scale(1.15)",setTimeout(()=>{this.style.transform="translateY(-50%) scale(1)"},150))})})}function w(){const t=document.querySelector('.auth-form input:not([type="hidden"])');t&&setTimeout(()=>{t.focus()},600);const e=document.getElementById("loginForm");if(e){const n=e.querySelector('input[name="email"], input[type="email"]'),o=e.querySelector('input[name="remember"]'),i=localStorage.getItem("LeJeepney_remembered_email");if(i&&n){n.value=i,o&&(o.checked=!0);const r=e.querySelector('input[type="password"]');r&&setTimeout(()=>r.focus(),600)}e.addEventListener("submit",function(){o?.checked&&n?localStorage.setItem("LeJeepney_remembered_email",n.value):localStorage.removeItem("LeJeepney_remembered_email")})}document.querySelectorAll('input[type="password"]').forEach(n=>{n.addEventListener("keyup",function(o){const i=this.closest(".form-group")?.querySelector(".caps-warning");if(o.getModifierState&&o.getModifierState("CapsLock")){if(!i){const r=document.createElement("div");r.className="caps-warning",r.innerHTML='<i class="fa-solid fa-exclamation-triangle"></i> Caps Lock is on',r.style.cssText=`
                        color: #F59E0B;
                        font-size: 0.75rem;
                        margin-top: 0.5rem;
                        display: flex;
                        align-items: center;
                        gap: 0.375rem;
                        animation: slideDown 0.2s ease;
                    `,this.closest(".form-group")?.appendChild(r)}}else i&&i.remove()})})}function v(){if(document.querySelectorAll(".btn").forEach(e=>{e.addEventListener("click",function(s){if(this.disabled)return;const n=document.createElement("span");n.className="ripple-effect";const o=this.getBoundingClientRect(),i=Math.max(o.width,o.height)*2,r=s.clientX-o.left-i/2,a=s.clientY-o.top-i/2;n.style.cssText=`
                position: absolute;
                width: ${i}px;
                height: ${i}px;
                left: ${r}px;
                top: ${a}px;
                background: rgba(255, 255, 255, 0.4);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out forwards;
                pointer-events: none;
            `,this.style.position="relative",this.style.overflow="hidden",this.appendChild(n),setTimeout(()=>n.remove(),600)})}),!document.getElementById("rippleStyles")){const e=document.createElement("style");e.id="rippleStyles",e.textContent=`
            @keyframes ripple {
                to {
                    transform: scale(1);
                    opacity: 0;
                }
            }
            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-5px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .shake {
                animation: shake 0.5s ease-in-out !important;
            }
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
        `,document.head.appendChild(e)}}function b(){document.querySelectorAll(".form-group input").forEach(e=>{const s=e.closest(".input-wrapper");s&&(e.addEventListener("focus",function(){s.classList.add("focused")}),e.addEventListener("blur",function(){s.classList.remove("focused")}))})}function E(){document.addEventListener("submit",function(t){if(!t.defaultPrevented){var e=t.target;if(e.classList.contains("auth-form")){var s=e.querySelector('button[type="submit"]');!s||s.disabled||(s.disabled=!0,s.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Please wait...')}}})}function L(){const t=document.querySelectorAll(".floating-element");if(t.length===0)return;let e=0,s=0,n=0,o=0;document.addEventListener("mousemove",function(r){e=(r.clientX/window.innerWidth-.5)*20,s=(r.clientY/window.innerHeight-.5)*20});function i(){n+=(e-n)*.05,o+=(s-o)*.05,t.forEach((r,a)=>{const m=1+a*.3,f=n*m,p=o*m;r.style.transform=`translate(${f}px, ${p}px)`}),requestAnimationFrame(i)}i()}function d(t,e="info"){document.querySelectorAll(".notification-toast").forEach(i=>i.remove());const s=document.createElement("div");s.className=`notification-toast notification-${e}`;const n={success:"fa-check-circle",error:"fa-times-circle",warning:"fa-exclamation-triangle",info:"fa-info-circle"},o={success:"#10B981",error:"#DC3545",warning:"#F59E0B",info:"#0C4E94"};s.innerHTML=`
        <i class="fa-solid ${n[e]||n.info}"></i>
        <span>${t}</span>
    `,s.style.cssText=`
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        padding: 1rem 1.5rem;
        background: ${o[e]||o.info};
        color: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 9999;
        transform: translateX(150%);
        transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        font-weight: 500;
        font-size: 0.9375rem;
    `,document.body.appendChild(s),requestAnimationFrame(()=>{s.style.transform="translateX(0)"}),setTimeout(()=>{s.style.transform="translateX(150%)",setTimeout(()=>s.remove(),400)},4e3)}window.showNotification=d;
