const modal=document.getElementById('modal');
function openModal(){modal.classList.add('show');modal.setAttribute('aria-hidden','false');document.body.style.overflow='hidden'}
function closeModal(){modal.classList.remove('show');modal.setAttribute('aria-hidden','true');document.body.style.overflow=''}
function toggleMenu(){const nav=document.querySelector('nav');nav.style.display=nav.style.display==='flex'?'none':'flex';nav.style.position='absolute';nav.style.top='76px';nav.style.left='0';nav.style.right='0';nav.style.padding='20px';nav.style.background='#fff';nav.style.flexDirection='column';nav.style.boxShadow='0 10px 20px rgba(0,0,0,.08)'}
function submitAppointment(e){e.preventDefault();document.getElementById('appointmentForm').hidden=true;document.getElementById('success').hidden=false}
window.addEventListener('click',e=>{if(e.target===modal)closeModal()});
window.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal()});
const dateInput=document.querySelector('input[type="date"]');
if(dateInput){const today=new Date();dateInput.min=new Date(today.getTime()-today.getTimezoneOffset()*60000).toISOString().split('T')[0]}
