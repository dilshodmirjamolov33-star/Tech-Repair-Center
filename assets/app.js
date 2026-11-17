document.querySelectorAll('#r').forEach(i=>i.addEventListener('input',()=>{i.value=i.value.toUpperCase().replace(/[^A-Z0-9]/g,'')}));
document.querySelectorAll('.toast').forEach(t=>setTimeout(()=>t.remove(),4200));
document.querySelectorAll('form').forEach(f=>f.addEventListener('submit',()=>{const b=f.querySelector('button.btn');if(b){setTimeout(()=>b.disabled=true,0)}}));
