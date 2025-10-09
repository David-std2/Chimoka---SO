// public/scripts/main.js
document.addEventListener('DOMContentLoaded', () => {
  const APP = window.APP || {};
  const tabla = APP.tabla || '';
  const columns = APP.columns || [];
  const pk = APP.pk || null;

  const searchInput = document.getElementById('search');
  const tableBody = document.querySelector('#data-table tbody');
  const progressScroll = document.getElementById('progressScroll');
  const tw = document.querySelector('.table-wrap');
  const scrollInner = document.getElementById('scrollInner');

  // Live search
  if (searchInput && tableBody) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.trim().toLowerCase();
      for (const row of tableBody.rows) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
      }
      if (typeof updateStickySpace === 'function') updateStickySpace();
      if (typeof updateProgressFromScroll === 'function') updateProgressFromScroll();
    });
  }

  // ===== Scroll progress bar sync =====
  function updateProgressFromScroll() {
    if (!tw || !scrollInner || !progressScroll) return;
    const maxScroll = Math.max(0, tw.scrollWidth - tw.clientWidth);
    if (maxScroll === 0) {
      scrollInner.style.width = '0%';
      return;
    }
    const pct = (tw.scrollLeft / maxScroll) * 100;
    scrollInner.style.width = Math.min(Math.max(pct, 0), 100) + '%';
  }

  if (tw && scrollInner && progressScroll) {
    tw.addEventListener('scroll', updateProgressFromScroll);
    setTimeout(updateProgressFromScroll, 60);

    let dragging = false;

    function scrollToPosition(clientX) {
      const rect = progressScroll.getBoundingClientRect();
      const trackWidth = rect.width || 0;
      if (trackWidth <= 0) return;

      const x = Math.max(0, Math.min(clientX - rect.left, trackWidth));
      const denom = trackWidth;
      const scrollable = Math.max(0, tw.scrollWidth - tw.clientWidth);
      if (scrollable <= 0) return;

      const scrollValue = (x / denom) * scrollable;
      if (Number.isFinite(scrollValue)) {
        tw.scrollLeft = scrollValue;
        updateProgressFromScroll();
      }
    }

    // Clic y arrastre
    progressScroll.addEventListener('click', ev => {
      try { scrollToPosition(ev.clientX); } catch (e) { /* silent */ }
    });

    progressScroll.addEventListener('mousedown', ev => {
      dragging = true;
      document.body.classList.add('no-select');
      try { scrollToPosition(ev.clientX); } catch (e) { /* silent */ }
    });

    window.addEventListener('mousemove', ev => {
      if (!dragging) return;
      try { scrollToPosition(ev.clientX); } catch (e) { /* silent */ }
    });

    window.addEventListener('mouseup', () => {
      if (!dragging) return;
      dragging = false;
      document.body.classList.remove('no-select');
    });

    // Touch (móvil)
    progressScroll.addEventListener('touchstart', ev => {
      dragging = true;
      try { scrollToPosition(ev.touches[0].clientX); } catch (e) { /* silent */ }
    }, { passive: true });

    window.addEventListener('touchmove', ev => {
      if (!dragging) return;
      try { scrollToPosition(ev.touches[0].clientX); } catch (e) { /* silent */ }
    }, { passive: true });

    window.addEventListener('touchend', () => { dragging = false; });
  }

  // Modal view / create / edit / delete handlers
  const verBtns = document.querySelectorAll('.ver');
  const modalVerEl = document.getElementById('modalVer');
  const modalFormEl = document.getElementById('modalForm');
  const modalVer = modalVerEl ? new bootstrap.Modal(modalVerEl) : null;
  const modalForm = modalFormEl ? new bootstrap.Modal(modalFormEl) : null;

  // ===== Ver registro =====
  verBtns.forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      const body = document.getElementById('modalVerBody');
      body.innerHTML = '<p class="text-muted text-center">Cargando...</p>';
      if (modalVer) modalVer.show();
      try {
        const res = await fetch('tables/read_table.php?tabla=' + encodeURIComponent(tabla) + '&id=' + encodeURIComponent(id));
        if (res.ok) {
          const data = await res.json();
          if (data && data.row) {
            let html = '<div class="table-responsive"><table class="table table-sm">';
            for (const k in data.row) {
              html += `<tr><th style="width:35%">${k}</th><td>${String(data.row[k])}</td></tr>`;
            }
            html += '</table></div>';
            body.innerHTML = html;
            return;
          }
        }
      } catch (e) { /* fallback */ }

      const tr = btn.closest('tr');
      const cells = Array.from(tr.children).slice(0, -1);
      let html = '<div class="table-responsive"><table class="table table-sm">';
      cells.forEach((td, i) => {
        const col = columns[i] ?? ('col' + i);
        html += `<tr><th style="width:35%">${col}</th><td>${td.innerText}</td></tr>`;
      });
      html += '</table></div>';
      body.innerHTML = html;
    });
  });

  // ===== Crear / Editar =====
  document.getElementById('btn-new')?.addEventListener('click', () => openFormModal('create'));
  document.querySelectorAll('.editar').forEach(btn => btn.addEventListener('click', () => openFormModal('edit', btn.dataset.id)));

  async function openFormModal(mode='create', id=null) {
    const titleEl = document.getElementById('modalFormTitle');
    const bodyEl = document.getElementById('modalFormBody');
    titleEl.textContent = mode === 'create' ? 'Nuevo registro' : 'Editar registro #' + id;
    let rowData = {};

    if (mode === 'edit' && id) {
      try {
        const res = await fetch('tables/read_table.php?tabla=' + encodeURIComponent(tabla) + '&id=' + encodeURIComponent(id));
        if (res.ok) {
          const j = await res.json();
          if (j && j.row) rowData = j.row;
        }
      } catch (e) {
        const tr = document.querySelector(`.editar[data-id="${id}"]`)?.closest('tr');
        if (tr) {
          const cells = Array.from(tr.children).slice(0, -1);
          columns.forEach((c, idx) => rowData[c] = (cells[idx] ? cells[idx].innerText : ''));
        }
      }
    }

    let html = '<form id="formDynamic" novalidate>';
    html += '<div class="row g-3">';
    if (!columns || columns.length === 0) {
      html += '<div class="col-12"><p class="text-muted">No hay esquema para esta tabla.</p></div>';
    } else {
      columns.forEach((c) => {
        const value = (rowData[c] ?? '')?.toString().replaceAll('"', '&quot;');
        const isPk = (c === pk);
        html += `<div class="col-md-6"><label class="form-label">${c}</label>
                 <input name="${c}" class="form-control" value="${value}" ${isPk && mode==='create' ? 'readonly' : ''}></div>`;
      });
    }
    html += '</div>';

    bodyEl.innerHTML = html;

    const formEl = document.getElementById('formDynamic');
    if (!formEl) return;
    formEl.addEventListener('submit', async (ev) => {
      ev.preventDefault();

      const data = {};
      for (const el of formEl.elements) {
        if (!el.name) continue;
        data[el.name] = el.value;
      }

      try {
        const endpoint = 'tables/' + (mode === 'create' ? 'create_table.php' : 'update_table.php');
        const url = mode === 'edit' && id ? `${endpoint}?id=${encodeURIComponent(id)}` : endpoint;
        const res = await fetch(url, {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ tabla, mode, id, data })
        });
        if (res.ok) {
          const j = await res.json();
          if (j.success) {
            if (modalForm) modalForm.hide();
            location.reload();
            return;
          } else {
            alert('Error: ' + (j.message || 'No guardado'));
          }
        } else {
          alert('Error en la petición al servidor.');
        }
      } catch (e) {
        alert('Error al guardar. Revisa la conexión con PHP.');
      }
    });

    if (modalForm) modalForm.show();
  }

  // ===== Eliminar registro =====
  document.querySelectorAll('.eliminar').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      if (!confirm('¿Eliminar este registro?')) return;
        try {
          const res = await fetch('tables/delete_table.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ tabla, id })
          });
        
          const text = await res.text();
          let j;
          try {
            j = JSON.parse(text);
          } catch (e) {
            console.error('Respuesta PHP no es JSON:', text);
            alert('Error en PHP: ' + text);
            return;
          }
        
          if (j.success) {
            alert('Eliminado correctamente.');
            location.reload();
          } else  alert('Error: ' + (j.message || 'No eliminado'));
        
        } catch (e) {
          alert('Error de conexión con PHP: ' + e.message);
        }  
    });
  });

  // ===== sticky column fix =====
  function updateStickySpace() {
    const table = document.querySelector('.table');
    const tw = document.querySelector('.table-wrap');
    if (!table || !tw) return;

    const stickyEls = table.querySelectorAll('.sticky-col');
    if (!stickyEls || stickyEls.length === 0) return;

    stickyEls.forEach(el => {
      el.style.right = '0px';
      el.style.zIndex = '45';
      el.style.background = el.closest('thead') ? 'var(--accent-dark)' : '#fff';
      el.style.boxShadow = '-8px 0 18px rgba(15,30,37,0.06)';
    });

    table.querySelectorAll('.spacer-col').forEach(el => el.remove());
    tw.style.paddingRight = '0px';

    try {
      if (typeof updateProgressFromScroll === 'function') updateProgressFromScroll();
    } catch (e) { /* silent */ }
  }

  updateStickySpace();
  let _tmoSticky;
  window.addEventListener('resize', () => {
    clearTimeout(_tmoSticky);
    _tmoSticky = setTimeout(() => {
      updateStickySpace();
      if (typeof updateProgressFromScroll === 'function') updateProgressFromScroll();
    }, 120);
  });

  if (window.MutationObserver) {
    const moSticky = new MutationObserver(() => {
      clearTimeout(_tmoSticky);
      _tmoSticky = setTimeout(() => {
        updateStickySpace();
        if (typeof updateProgressFromScroll === 'function') updateProgressFromScroll();
      }, 80);
    });
    const table = document.querySelector('.table');
    if (table) moSticky.observe(table, { childList: true, subtree: true, attributes: true });
  }

}); // DOMContentLoaded
