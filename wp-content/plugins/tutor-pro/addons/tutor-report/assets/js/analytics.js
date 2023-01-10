(()=>{function b(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var r=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=r){var a,n,o=[],l=!0,i=!1;try{for(r=r.call(t);!(l=(a=r.next()).done)&&(o.push(a.value),!e||o.length!==e);l=!0);}catch(t){i=!0,n=t}finally{try{l||null==r.return||r.return()}finally{if(i)throw n}}return o}}(t,e)||i(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function h(t,e){var r,a="undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(!a){if(Array.isArray(t)||(a=i(t))||e&&t&&"number"==typeof t.length)return a&&(t=a),r=0,{s:e=function(){},n:function(){return r>=t.length?{done:!0}:{done:!1,value:t[r++]}},e:function(t){throw t},f:e};throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var n,o=!0,l=!1;return{s:function(){a=a.call(t)},n:function(){var t=a.next();return o=t.done,t},e:function(t){l=!0,n=t},f:function(){try{o||null==a.return||a.return()}finally{if(l)throw n}}}}function i(t,e){if(t){if("string"==typeof t)return a(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);return"Map"===(r="Object"===r&&t.constructor?t.constructor.name:r)||"Set"===r?Array.from(t):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?a(t,e):void 0}}function a(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,a=new Array(e);r<e;r++)a[r]=t[r];return a}window.onload=function(){var t=document.getElementById("tutor_analytics_search_icon");t&&(t.onclick=function(){document.getElementById("tutor_analytics_search_form").submit()});var e,r=h(document.querySelectorAll(".tutor-admin-report-frequency"));try{for(r.s();!(e=r.n()).done;)e.value.onclick=function(t){var e,r,t=t.target.dataset.key;"custom"!==t&&((r=(e=new URL(window.location.href)).searchParams).has("start_date")&&r.delete("start_date"),r.has("end_date")&&r.delete("end_date"),r.set("period",t),window.location=e)}}catch(t){r.e(t)}finally{r.f()}var a,n=h(_tutor_analytics);try{for(n.s();!(a=n.n()).done;){for(var o=a.value,l=document.getElementById("".concat(o.id,"_canvas")).getContext("2d"),i=[],s=[],c=[],u=0,d=Object.entries(o.data);u<d.length;u++){var f=b(d[u],2),m=(f[0],f[1]),g=new Date(m.date_format).toLocaleDateString("en-US",{month:"short",day:"numeric"});i.push(g),s.push(m.total),m.fees&&c.push(m.fees)}var y=[];y.push({label:o.label,backgroundColor:"#3057D5",borderColor:"#3057D5",data:s,borderWidth:2,fill:!1,lineTension:0}),c.length&&y.push({label:o.label2,backgroundColor:"rgba(200, 0, 0, 1)",borderColor:"rgba(200, 0, 0, 1)",data:c,borderWidth:2,fill:!1,lineTension:0}),new Chart(l,{type:"line",data:{labels:i,datasets:y},options:{scales:{yAxes:[{ticks:{min:0,beginAtZero:!0,callback:function(t,e,r){if(Math.floor(t)===t)return t}}}]},legend:{display:!1}}})}}catch(t){n.e(t)}finally{n.f()}document.addEventListener("click",function(t){var e="data-tutor-modal-target",r="data-tutor-modal-close";(t.target.hasAttribute(e)||t.target.closest("[".concat(e,"]")))&&(t.preventDefault(),e=(t.target.hasAttribute(e)?t.target:t.target.closest("[".concat(e,"]"))).getAttribute(e),document.getElementById(e)),(t.target.hasAttribute(r)||t.target.classList.contains("tutor-modal-overlay")||t.target.closest("[".concat(r,"]")))&&(t.preventDefault(),document.querySelectorAll(".tutor-modal.tutor-is-active").forEach(function(t){t.classList.remove("tutor-is-active")}))})},jQuery(document).ready(function(r){r(".analytics_view_course_progress").on("click",function(t){var e;(t=t).preventDefault(),(e=t.target).classList.add("is-loading"),e.setAttribute("disabled","disabled"),r.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{course_id:t.target.dataset.course_id,total_progress:t.target.dataset.total_progress,total_lesson:t.target.dataset.total_lesson,completed_lesson:t.target.dataset.completed_lesson,total_assignment:t.target.dataset.total_assignment,completed_assignment:t.target.dataset.completed_assignment,total_quiz:t.target.dataset.total_quiz,completed_quiz:t.target.dataset.completed_quiz,student_id:t.target.dataset.student_id,action:"view_progress"},beforeSend:function(){},success:function(t){document.getElementById("tutor_progress_modal_content").innerHTML=t,document.getElementById("modal-course-overview").classList.add("tutor-is-active")},complete:function(){e.classList.remove("is-loading"),e.removeAttribute("disabled"),tutorAccordion()}})})})})();