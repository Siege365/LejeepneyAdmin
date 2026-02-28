document.addEventListener("DOMContentLoaded",function(){document.querySelectorAll(".alert").forEach(t=>{setTimeout(()=>{t.style.opacity="0",t.style.transform="translateY(-10px)",setTimeout(()=>{t.remove()},300)},5e3)});const e=document.querySelector(".settings-form");e&&e.addEventListener("submit",function(t){t.preventDefault();const o=document.getElementById("base_fare"),s=parseFloat(o.value),l=document.getElementById("fare_per_km"),i=parseFloat(l.value);if(isNaN(s)||s<0)return c("Please enter a valid base fare amount","error"),o.focus(),!1;if(s>999999)return c("Base fare cannot exceed ₱999,999.00","error"),o.focus(),!1;if(isNaN(i)||i<0)return c("Please enter a valid fare per kilometer amount","error"),l.focus(),!1;if(i>999999)return c("Fare per kilometer cannot exceed ₱999,999.00","error"),l.focus(),!1;m(s,i)});const a=document.getElementById("base_fare"),n=document.getElementById("fare_per_km");[a,n].forEach(t=>{t&&(t.addEventListener("input",function(){this.classList.remove("is-invalid");const o=this.parentElement.parentElement.querySelector(".form-error");o&&o.remove()}),t.addEventListener("blur",function(){const o=parseFloat(this.value);isNaN(o)||(this.value=o.toFixed(2))}))})});function m(r,e){const a=document.getElementById("confirmSettingsModal"),n=document.getElementById("confirmBaseFare"),t=document.getElementById("confirmFarePerKm");n.textContent="₱"+parseFloat(r).toFixed(2),t.textContent="₱"+parseFloat(e).toFixed(2),a.style.display="flex",requestAnimationFrame(()=>{a.classList.add("active")})}function f(){const r=document.getElementById("confirmSettingsModal");r.classList.remove("active"),setTimeout(()=>{r.style.display="none"},200)}function d(){const r=document.querySelector(".settings-form"),e=r.querySelector('button[type="submit"]');f(),e&&(e.disabled=!0,e.innerHTML='<i class="fas fa-spinner fa-spin"></i> Saving...'),r.submit()}function c(r,e="info",a=3e3){document.querySelectorAll(".toast").forEach(o=>o.remove());const n=document.createElement("div");n.className=`toast toast-${e}`,n.style.cssText=`
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        padding: 1rem 1.5rem;
        background: ${e==="success"?"#10B981":e==="error"?"#EF4444":e==="warning"?"#F59E0B":"#3B82F6"};
        color: white;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 9999;
        transform: translateX(120%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 500;
        font-size: 0.9375rem;
    `;const t={success:"fa-circle-check",error:"fa-circle-exclamation",warning:"fa-triangle-exclamation",info:"fa-circle-info"};n.innerHTML=`
        <i class="fa-solid ${t[e]||t.info}"></i>
        <span>${r}</span>
    `,document.body.appendChild(n),requestAnimationFrame(()=>{n.style.transform="translateX(0)"}),setTimeout(()=>{n.style.transform="translateX(120%)",setTimeout(()=>n.remove(),300)},a)}window.showConfirmationModal=m;window.closeConfirmationModal=f;window.confirmSaveSettings=d;
