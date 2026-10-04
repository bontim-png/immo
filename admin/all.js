
function setupRichEditor(root){
  const content=root.querySelector('.rich-content'), source=root.querySelector('.rich-source');
  if(!content||!source)return;
  const sync=()=>{ source.value=content.innerHTML; };
  root.querySelectorAll('[data-cmd]').forEach(btn=>btn.addEventListener('mousedown',e=>{e.preventDefault();content.focus();document.execCommand(btn.dataset.cmd,false,null);sync();}));
  root.querySelectorAll('[data-block]').forEach(btn=>btn.addEventListener('mousedown',e=>{e.preventDefault();content.focus();document.execCommand('formatBlock',false,btn.dataset.block);sync();}));
  content.addEventListener('input',sync);
  content.closest('form')?.addEventListener('submit',sync);
  sync();
}

(function(){
  const tabs=[...document.querySelectorAll('.property-tab')];
  const panels=[...document.querySelectorAll('.tab-panel')];
  const mediaPanels={photos:document.getElementById('photos'),videos:document.getElementById('videos'),documents:document.getElementById('documents')};
  const activate=(name,replace=true)=>{
    tabs.forEach(t=>{const on=t.dataset.tab===name;t.classList.toggle('active',on);t.setAttribute('aria-selected',on?'true':'false');});
    panels.forEach(p=>p.classList.toggle('active',p.dataset.tabPanel===name));
    Object.entries(mediaPanels).forEach(([key,el])=>{if(el)el.style.display=key===name?'block':'none';});
    if(replace){try{history.replaceState(null,'','#'+name);}catch(e){}}
    window.scrollTo({top:0,behavior:'smooth'});
  };
  tabs.forEach(t=>t.addEventListener('click',()=>activate(t.dataset.tab)));
  const initial=(location.hash||'').replace('#','');
  activate(tabs.some(t=>t.dataset.tab===initial)?initial:'general',false);
})();
document.querySelectorAll('.rich-editor').forEach(setupRichEditor);

const csrf='<?=e($csrf)?>';
const propertyId='<?=$id?>';

function setupFileDropzone(zoneId,inputId,previewId,buttonId,allowed,maxBytes,kind){
  const zone=document.getElementById(zoneId),input=document.getElementById(inputId),preview=document.getElementById(previewId),button=document.getElementById(buttonId);
  if(!zone||!input||!preview||!button)return;
  let files=[];
  const valid=f=>{if(!f||f.size>maxBytes)return false; if(allowed.includes(f.type))return true; const n=(f.name||'').toLowerCase(); return kind==='photo' && /\.(jpe?g|png|webp)$/i.test(n);};
  const sync=()=>{try{const dt=new DataTransfer();files.forEach(f=>dt.items.add(f));input.files=dt.files;}catch(e){}};
  const render=()=>{preview.innerHTML='';files.forEach(f=>{const box=document.createElement('div');box.className='preview';if(kind==='photo'){const img=document.createElement('img');img.src=URL.createObjectURL(f);img.alt=f.name;box.appendChild(img);}else{const icon=document.createElement('div');icon.style.fontSize='34px';icon.textContent='🎬';box.appendChild(icon);}const label=document.createElement('div');label.className='small';label.textContent=f.name;box.appendChild(label);preview.appendChild(box);});button.disabled=!files.length;};
  const add=list=>{Array.from(list||[]).filter(valid).forEach(f=>{if(!files.some(x=>x.name===f.name&&x.size===f.size&&x.lastModified===f.lastModified))files.push(f);});sync();render();};
  const open=()=>input.click();
  zone.addEventListener('click',e=>{if(e.target!==input){e.preventDefault();open();}});
  zone.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();open();}});
  input.addEventListener('change',()=>add(input.files));
  ['dragenter','dragover'].forEach(ev=>zone.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();zone.classList.add('dragover');if(e.dataTransfer)e.dataTransfer.dropEffect='copy';}));
  ['dragleave','drop'].forEach(ev=>zone.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();if(ev==='drop')add(e.dataTransfer.files);zone.classList.remove('dragover');}));
}

setupFileDropzone('dropzone','photoInput','uploadPreview','uploadButton',['image/jpeg','image/png','image/webp'],12*1024*1024,'photo');
setupFileDropzone('videoDropzone','videoInput','videoPreview','videoUploadButton',['video/mp4','video/webm','video/ogg','video/quicktime'],250*1024*1024,'video');
setupFileDropzone('documentDropzone','documentInput','documentPreview','documentUploadButton',['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','text/csv','text/plain','application/rtf','application/vnd.oasis.opendocument.text','application/vnd.oasis.opendocument.spreadsheet','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation'],25*1024*1024,'document');

const office=document.getElementById('office'),agent=document.getElementById('agent');
if(office&&agent){const opts=[...agent.options];const filterAgents=()=>opts.forEach(o=>{if(!o.value)return;o.hidden=o.dataset.office!==office.value;if(o.hidden&&o.selected)agent.value='';});office.addEventListener('change',filterAgents);filterAgents();}

const poolBox=document.querySelector('input[data-feature-code="pool"]'),poolModal=document.getElementById('poolModal');
const poolLen=document.getElementById('pool_length_m'),poolWid=document.getElementById('pool_width_m'),poolDep=document.getElementById('pool_depth_m');
const poolModalLen=document.getElementById('poolModalLength'),poolModalWid=document.getElementById('poolModalWidth'),poolModalDep=document.getElementById('poolModalDepth'),poolTile=document.querySelector('[data-feature-tile][data-feature-code="pool"]');
const openPoolModal=()=>{if(!poolModal)return;poolModalLen.value=poolLen?.value||'';poolModalWid.value=poolWid?.value||'';poolModalDep.value=poolDep?.value||'';poolModal.hidden=false;document.body.classList.add('modal-open');setTimeout(()=>poolModalLen?.focus(),30);};
const closePoolModal=()=>{if(!poolModal)return;poolModal.hidden=true;document.body.classList.remove('modal-open');};
const updatePoolTile=()=>{if(!poolTile)return;const on=!!poolBox?.checked;poolTile.classList.toggle('selected',on);let summary='';if(on&&poolLen?.value&&poolWid?.value){summary=`${poolLen.value} × ${poolWid.value} m`;if(poolDep?.value)summary+=` × ${poolDep.value} m`;}poolTile.dataset.poolSummary=summary;poolTile.title=summary?`Pool dimensions: ${summary}`:(window.ImmoI18n&&window.ImmoI18n.add_pool_dimensions)||'Add pool dimensions';const small=poolTile.querySelector('.feature-copy small');if(small)small.textContent=summary||(window.ImmoI18n&&window.ImmoI18n.add_dimensions)||'Add dimensions';};
if(poolBox){poolBox.addEventListener('change',()=>{if(poolBox.checked)openPoolModal();else{poolLen.value='';poolWid.value='';poolDep.value='';updatePoolTile();}});updatePoolTile();}
poolTile?.addEventListener('click',e=>{if(e.target===poolBox)return;if(poolBox.checked)openPoolModal();else{poolBox.checked=true;openPoolModal();}});
document.getElementById('poolModalSave')?.addEventListener('click',()=>{if(!poolModalLen.value||!poolModalWid.value){alert((window.ImmoI18n&&window.ImmoI18n.enter_pool_size)||'Please enter pool length and width.');return;}poolLen.value=poolModalLen.value;poolWid.value=poolModalWid.value;poolDep.value=poolModalDep.value;poolBox.checked=true;updatePoolTile();closePoolModal();});
const cancelPool=()=>{if(!poolLen.value&&!poolWid.value&&!poolDep.value)poolBox.checked=false;closePoolModal();updatePoolTile();};
document.getElementById('poolModalCancel')?.addEventListener('click',cancelPool);document.getElementById('poolModalClose')?.addEventListener('click',cancelPool);poolModal?.addEventListener('click',e=>{if(e.target===poolModal)cancelPool();});
const photoGrid=document.getElementById('photoGrid');let draggedPhoto=null;
if(photoGrid){
  photoGrid.querySelectorAll('.photo').forEach(card=>{
    // Make the whole card draggable for reliable Safari/Chrome/Firefox behaviour.
    // The drag handle remains the visual cue; interactive controls are excluded.
    card.setAttribute('draggable','true');
    card.addEventListener('dragstart',e=>{
      if(e.target.closest('button,form,input,select,a')){e.preventDefault();return;}
      draggedPhoto=card;
      card.classList.add('dragging');
      if(e.dataTransfer){e.dataTransfer.effectAllowed='move';e.dataTransfer.setData('text/plain',card.dataset.mediaId||'');}
    });
    card.addEventListener('dragend',()=>{
      card.classList.remove('dragging');
      photoGrid.querySelectorAll('.photo').forEach(x=>x.classList.remove('drag-over'));
      draggedPhoto=null;
    });
    card.addEventListener('dragover',e=>{
      if(!draggedPhoto||draggedPhoto===card)return;
      e.preventDefault();
      if(e.dataTransfer)e.dataTransfer.dropEffect='move';
      card.classList.add('drag-over');
    });
    card.addEventListener('dragleave',()=>card.classList.remove('drag-over'));
    card.addEventListener('drop',async e=>{
      e.preventDefault();
      e.stopPropagation();
      card.classList.remove('drag-over');
      if(!draggedPhoto||draggedPhoto===card)return;
      // Dropping ON a card means inserting before that card.
      card.parentNode.insertBefore(draggedPhoto,card);
      await savePhotoOrder();
    });
  });
}
async function savePhotoOrder(){const order=[...photoGrid.querySelectorAll('.photo')].map(x=>x.dataset.mediaId);const fd=new FormData();fd.append('_csrf',csrf);fd.append('property_id',propertyId);fd.append('media_type','photo');order.forEach(x=>fd.append('order[]',x));try{const r=await fetch('/immobilier/admin/property-media-reorder.php',{method:'POST',body:fd,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});const j=await r.json();if(!j.success)throw new Error(j.message||'Could not save photo order.');}catch(e){alert(e.message);location.reload();}}

const videoGrid=document.getElementById('videoGrid');let draggedVideo=null;if(videoGrid){videoGrid.querySelectorAll('.video-card').forEach(card=>{const handle=card.querySelector('.video-drag-handle');if(handle)handle.addEventListener('dragstart',e=>{draggedVideo=card;card.classList.add('dragging');e.dataTransfer.effectAllowed='move';e.dataTransfer.setData('text/plain',card.dataset.mediaId);});card.addEventListener('dragover',e=>{if(!draggedVideo||draggedVideo===card)return;e.preventDefault();card.classList.add('drag-over');});card.addEventListener('dragleave',()=>card.classList.remove('drag-over'));card.addEventListener('drop',async e=>{e.preventDefault();card.classList.remove('drag-over');if(!draggedVideo||draggedVideo===card)return;card.before(draggedVideo);await saveVideoOrder();});card.addEventListener('dragend',()=>{card.classList.remove('dragging');draggedVideo=null;videoGrid.querySelectorAll('.video-card').forEach(x=>x.classList.remove('drag-over'));});});}
async function saveVideoOrder(){const order=[...videoGrid.querySelectorAll('.video-card')].map(x=>x.dataset.mediaId);const fd=new FormData();fd.append('_csrf',csrf);fd.append('property_id',propertyId);fd.append('media_type','video');order.forEach(x=>fd.append('order[]',x));try{const r=await fetch('/immobilier/admin/property-media-reorder.php',{method:'POST',body:fd,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});const j=await r.json();if(!j.success)throw new Error(j.message||'Could not save video order.');}catch(e){alert(e.message);location.reload();}}

const dfilter=document.getElementById('documentCategoryFilter'),dgrid=document.getElementById('documentGrid');if(dfilter&&dgrid)dfilter.addEventListener('change',()=>dgrid.querySelectorAll('.document-row').forEach(r=>{r.style.display=(dfilter.value==='all'||r.dataset.category===dfilter.value)?'grid':'none';}));
const docGrid=document.getElementById('documentGrid');let draggedDoc=null;if(docGrid){docGrid.querySelectorAll('.document-row').forEach(row=>{row.addEventListener('dragstart',e=>{if(e.target.closest('form,a,button,input,select')){e.preventDefault();return;}draggedDoc=row;row.classList.add('dragging');e.dataTransfer.effectAllowed='move';});row.addEventListener('dragover',e=>{if(!draggedDoc||draggedDoc===row)return;e.preventDefault();row.classList.add('drag-over');});row.addEventListener('dragleave',()=>row.classList.remove('drag-over'));row.addEventListener('drop',async e=>{e.preventDefault();row.classList.remove('drag-over');if(!draggedDoc||draggedDoc===row)return;row.before(draggedDoc);await saveDocumentOrder();});row.addEventListener('dragend',()=>{row.classList.remove('dragging');draggedDoc=null;docGrid.querySelectorAll('.document-row').forEach(x=>x.classList.remove('drag-over'));});});}
async function saveDocumentOrder(){const order=[...docGrid.querySelectorAll('.document-row')].map(x=>x.dataset.documentId);const fd=new FormData();fd.append('_csrf',csrf);fd.append('property_id',propertyId);order.forEach(x=>fd.append('order[]',x));try{const r=await fetch('/immobilier/admin/property-document-reorder.php',{method:'POST',body:fd,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});const j=await r.json();if(!j.success)throw new Error(j.message||'Could not save document order.');}catch(e){alert(e.message);location.reload();}}



(function(){
 const form=document.getElementById('propertyForm'); const status=document.getElementById('autosaveStatus'); if(!form||!status) return;
 let timer=null, saving=false, dirty=false;
 const syncRich=()=>document.querySelectorAll('.rich-editor').forEach(setupRichEditor);
 const setStatus=(text,cls='')=>{status.textContent=text;status.className='autosave-status '+cls;};
 const save=async()=>{if(<?= $id>0?'false':'true' ?>||saving||!dirty)return; saving=true; setStatus('Saving…','saving'); document.querySelectorAll('.rich-editor').forEach(root=>{const c=root.querySelector('.rich-content'),src=root.querySelector('.rich-source');if(c&&src)src.value=c.innerHTML;}); const fd=new FormData(form); fd.set('action','save'); try{const r=await fetch(window.location.href,{method:'POST',body:fd,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}}); const j=await r.json(); if(!r.ok||!j.success)throw new Error(j.message||'Save failed'); setStatus('Saved just now','saved'); dirty=false;}catch(e){setStatus('Save failed · Retry','error');}finally{saving=false;}};
 const schedule=()=>{dirty=true;setStatus('Unsaved changes');clearTimeout(timer);timer=setTimeout(save,1300);};
 form.querySelectorAll('input:not([type=hidden]),select,textarea').forEach(el=>{el.addEventListener('input',schedule);el.addEventListener('change',schedule);});
 document.querySelectorAll('.rich-content').forEach(el=>el.addEventListener('input',schedule));
 window.addEventListener('beforeunload',e=>{if(dirty){e.preventDefault();e.returnValue='';}});
 document.querySelector('.property-actions .save')?.addEventListener('click',()=>{dirty=false;setStatus('Saving…','saving');});
})();
(function(){
 const mapEl=document.getElementById('propertyMap'); const post=document.getElementById('locationPostcode'), city=document.getElementById('locationCity'), st=document.getElementById('mapStatus'); if(!mapEl||typeof L==='undefined')return;
 let map=L.map(mapEl,{zoomControl:true,scrollWheelZoom:false}).setView([46.6,2.3],5); L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'&copy; OpenStreetMap contributors'}).addTo(map); let layer=null;
 const setStatus=t=>{if(st)st.textContent=t;};
 const draw=async()=>{const q=(post?.value||'').trim()?(post.value+' France'):(city?.value||'').trim()?(city.value+' France'):''; if(!q){setStatus('Enter a postcode or city');return;} setStatus('Loading area…'); try{const url='https://nominatim.openstreetmap.org/search?format=jsonv2&polygon_geojson=1&limit=1&countrycodes=fr&q='+encodeURIComponent(q);const r=await fetch(url,{headers:{Accept:'application/json'}});const a=await r.json();if(!a.length)throw new Error('not found');const x=a[0];if(layer)map.removeLayer(layer); if(x.geojson&&(x.geojson.type==='Polygon'||x.geojson.type==='MultiPolygon')){layer=L.geoJSON(x.geojson,{style:{color:'#315cf6',weight:2,fillColor:'#315cf6',fillOpacity:.12}}).addTo(map);map.fitBounds(layer.getBounds(),{padding:[20,20]});setStatus('Approximate area');}else{const lat=+x.lat,lon=+x.lon;layer=L.circle([lat,lon],{radius:10000,color:'#315cf6',fillColor:'#315cf6',fillOpacity:.10,weight:2}).addTo(map);map.fitBounds(layer.getBounds(),{padding:[20,20]});setStatus('10 km approximate area');}}catch(e){setStatus('Area unavailable');}};
 draw(); post?.addEventListener('change',draw);city?.addEventListener('change',draw);
})();
(function(){
 const p=document.getElementById('poolBox'); if(!p)return; const tile=p.closest('.feature-tile'); const len=document.getElementById('pool_length_m'),wid=document.getElementById('pool_width_m'),dep=document.getElementById('pool_depth_m'); const check=()=>{if(p.checked && (!len.value||!wid.value)){tile?.classList.add('invalid');}}; p.form?.addEventListener('submit',e=>{if(p.checked&&(!len.value||!wid.value)){e.preventDefault();document.querySelector('[data-tab=features]')?.click();alert((window.ImmoI18n&&window.ImmoI18n.enter_pool_size)||'Please enter pool length and width.');}});})();
(function(){
 const percent=document.getElementById('completionPercent'),bar=document.getElementById('completionBar'); if(!percent||!bar)return; const calc=()=>{let n=0,total=6;const val=n=>!!String(n||'').trim(); if(val(document.querySelector('[name=property_type_id]')?.value)&&val(document.querySelector('[name=reference]')?.value)&&val(document.querySelector('[name=title]')?.value))n++; const rich=[...document.querySelectorAll('.rich-content')]; if(rich.some(x=>x.textContent.trim().length>0))n++; if(document.querySelector('[name="features[]"]:checked'))n++; if(document.querySelectorAll('#photoGrid .photo').length)n++; if(document.querySelectorAll('#documentGrid .document-row').length)n++; if(document.querySelector('[name=is_published]')?.checked)n++; const pc=Math.round(n/total*100);percent.textContent=pc+'%';bar.style.width=pc+'%';}; calc();})();
