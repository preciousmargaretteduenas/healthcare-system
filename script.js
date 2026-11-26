const modal=document.getElementById('modal');
function openModal(){if(!modal)return;modal.classList.add('show');modal.setAttribute('aria-hidden','false');document.body.style.overflow='hidden'}
function closeModal(){if(!modal)return;modal.classList.remove('show');modal.setAttribute('aria-hidden','true');document.body.style.overflow=''}
function toggleMenu(){const nav=document.querySelector('nav');if(!nav)return;nav.style.display=nav.style.display==='flex'?'none':'flex';nav.style.position='absolute';nav.style.top='76px';nav.style.left='0';nav.style.right='0';nav.style.padding='20px';nav.style.background='#fff';nav.style.flexDirection='column';nav.style.boxShadow='0 10px 20px rgba(0,0,0,.08)'}
function submitAppointment(e){e.preventDefault();const form=document.getElementById('appointmentForm');if(!form)return;const email=form.querySelector('[name=email]').value;const code='PA-'+Math.random().toString(36).slice(2,8).toUpperCase();form.hidden=true;const box=document.getElementById('success');box.textContent='Request submitted! Tracking code: '+code+'. Prototype only: email preview for '+email+'; actual Gmail delivery requires PHP SMTP.';box.hidden=false}
window.addEventListener('click',e=>{if(e.target===modal)closeModal()});window.addEventListener('keydown',e=>{if(e.key==='Escape'){closeModal();closePatient()}});
const dateInput=document.querySelector('input[type="date"]');if(dateInput){const today=new Date();dateInput.min=new Date(today.getTime()-today.getTimezoneOffset()*60000).toISOString().split('T')[0]}
function openPatient(){const m=document.getElementById('patientModal');if(m){m.classList.add('show');document.body.style.overflow='hidden'}}
function closePatient(){const m=document.getElementById('patientModal');if(m){m.classList.remove('show');document.body.style.overflow=''}}
function registerPatient(e){e.preventDefault();closePatient();showToast('Patient registered successfully (prototype)');e.target.reset()}
function showToast(message){const t=document.getElementById('toast');if(!t)return;t.textContent='✓ '+message;t.classList.add('show');clearTimeout(window.toastTimer);window.toastTimer=setTimeout(()=>t.classList.remove('show'),2800)}
const patientModal=document.getElementById('patientModal');if(patientModal)patientModal.addEventListener('click',e=>{if(e.target===patientModal)closePatient()});

document.getElementById('appointmentForm').addEventListener('submit',submitAppointment);
