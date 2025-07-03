<?php $title = 'Connections - ' . APP_NAME; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-900">Connections</h1>
      <a href="/social" class="text-gray-600 hover:text-gray-900 flex items-center">
        <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back
      </a>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100">
      <div class="flex space-x-6 px-6 pt-6 text-lg font-semibold">
        <button class="tab-link active" data-target="following-tab">Following (<span id="following-count"><?= count($following) ?></span>)</button>
        <button class="tab-link" data-target="followers-tab">Followers (<span id="followers-count"><?= count($followers) ?></span>)</button>
        <button class="tab-link" data-target="friends-tab">Friends (<span id="friends-count"><?= count($mutualFollows) ?></span>)</button>
      </div>
      <div class="p-6 divide-y divide-gray-100">
        <!-- Following -->
        <div id="following-tab" class="tab-content">
          <?php if (empty($following)): ?>
            <p class="text-center py-12 text-gray-500">You are not following anyone yet.</p>
          <?php endif; ?>
          <?php foreach ($following as $user): ?>
            <?php $user['is_following']=true; include __DIR__.'/partials/user_row.php'; ?>
          <?php endforeach; ?>
        </div>
        <!-- Followers -->
        <div id="followers-tab" class="tab-content hidden">
          <?php if (empty($followers)): ?>
            <p class="text-center py-12 text-gray-500">No one is following you yet.</p>
          <?php endif; ?>
          <?php foreach ($followers as $user): ?>
            <?php $user['is_following']=in_array($user['uid'],array_column($following,'uid')); include __DIR__.'/partials/user_row.php'; ?>
          <?php endforeach; ?>
        </div>
        <!-- Friends -->
        <div id="friends-tab" class="tab-content hidden">
          <?php if (empty($mutualFollows)): ?>
            <p class="text-center py-12 text-gray-500">No mutual follows yet.</p>
          <?php endif; ?>
          <?php foreach ($mutualFollows as $user): ?>
            <?php $user['is_following']=true; include __DIR__.'/partials/user_row.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// tab switching
const tabs=document.querySelectorAll('.tab-link');
const panes=document.querySelectorAll('.tab-content');
tabs.forEach(btn=>btn.addEventListener('click',()=>{tabs.forEach(b=>b.classList.remove('active','text-blue-600')); panes.forEach(p=>p.classList.add('hidden')); btn.classList.add('active','text-blue-600'); document.getElementById(btn.dataset.target).classList.remove('hidden');}));

function showNotification(msg){ const d=document.createElement('div'); d.className='fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow z-50'; d.textContent=msg; document.body.appendChild(d); setTimeout(()=>d.remove(),3000); }

function updateCount(id,delta){const span=document.getElementById(id); if(!span) return; let n=parseInt(span.textContent)+delta; if(n<0) n=0; span.textContent=n;}

async function followUser(uid,btn){ try{ const r=await fetch(`/api/users/${uid}/follow`,{method:'POST'}); const j=await r.json(); if(j.success){ btn.textContent='Unfollow'; btn.classList.replace('bg-blue-500','bg-gray-500'); btn.onclick=()=>unfollowUser(uid,btn); updateCount('following-count',1);
 // move to Following list if not
 const row=document.getElementById(`user-row-${uid}`); const folList=document.getElementById('following-tab'); if(row && !folList.contains(row)){ folList.appendChild(row); }
 // if exists in followers and newly mutual add to friends
 const friends=document.getElementById('friends-tab'); if(!friends.querySelector(`#user-row-${uid}`)){ friends.appendChild(row.cloneNode(true)); updateCount('friends-count',1);} showNotification('Followed'); }else{showNotification(j.error||'Failed');}}catch(e){showNotification('Network error');} }

async function unfollowUser(uid,btn){try{ const r=await fetch(`/api/users/${uid}/unfollow`,{method:'DELETE'}); const j=await r.json(); if(j.success){ btn.textContent='Follow'; btn.classList.replace('bg-gray-500','bg-blue-500'); btn.onclick=()=>followUser(uid,btn); updateCount('following-count',-1); // remove from lists
 const rowFollowing=document.getElementById('following-tab').querySelector(`#user-row-${uid}`); if(rowFollowing) rowFollowing.remove(); const rowFriends=document.getElementById('friends-tab').querySelector(`#user-row-${uid}`); if(rowFriends){ rowFriends.remove(); updateCount('friends-count',-1);} showNotification('Unfollowed'); }else{showNotification(j.error||'Failed');}}catch(e){showNotification('Network error');}}
</script> 